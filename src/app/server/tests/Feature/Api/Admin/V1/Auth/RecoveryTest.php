<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Auth;

use AdminUser\Domain\Models\AdminUserId;
use AdminUser\Infrastructures\AdminUserRepository;
use Auth\Domain\Models\AdminUserPasskey;
use Auth\Domain\Models\AdminUserPasskeyRepositoryInterface;
use Auth\Domain\Models\RecoveryCode\ConsumptionStatus;
use Auth\Domain\Models\RecoveryCode\HashedCodeValue;
use Auth\Domain\Models\RecoveryCode\RecoveryCode;
use Auth\Domain\Models\RecoveryCode\RecoveryCodeId;
use Auth\Domain\Models\RecoveryCode\RecoveryCodeRepositoryInterface;
use Auth\Domain\Services\PasskeyAuthenticationResult;
use Auth\Domain\Services\PasskeyAuthenticatorInterface;
use Auth\Domain\Services\PasskeyRegistrationResult;
use Auth\Domain\Services\PasskeyStartResult;
use Auth\Domain\Services\RecoveryCode\RecoveryCodeHasherInterface;
use Auth\Route\AuthRouteMap;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\Fluent\AssertableJson;
use Override;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditTargetType;
use Tests\Support\Concerns\AssertsAuditLog;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class RecoveryTest extends DatabaseTestCase
{
    use AssertsAuditLog;
    use EntityFactory;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        config()->set([
            'auth.jwt.alg' => 'HS256',
            'auth.jwt.key' => str_repeat('k', 256),
        ]);
    }

    #[Test]
    public function canRecoverWithValidCodeAndAddsNewPasskey(): void
    {
        $this->bindPasskeyAuthenticator();
        $adminUserId = $this->storeAdminUserWithPasskey('example@example.com');
        $this->storeRecoveryCode($adminUserId, 'A3KP-9QXR');

        $authCeremonyId = $this->startRecovery('example@example.com', 'A3KP-9QXR');

        $this->postJson(route(AuthRouteMap::RecoveryFinish), [
            'authCeremonyId' => $authCeremonyId,
            'credential' => ['id' => 'new-credential-id'],
        ])->assertStatus(200)
            ->assertJson(
                static fn (AssertableJson $json) => $json->whereAllType([
                    'accessToken' => 'string',
                    'refreshTokenId' => 'string',
                    'refreshToken' => 'string',
                ])->etc(),
            );

        // 新しい passkey が追加され、既存の passkey も残っている
        $this->assertSame(2, DB::table('admin_user_passkeys')->count());
        $this->assertNotNull(
            $this->app->make(AdminUserPasskeyRepositoryInterface::class)->findByCredentialId('new-credential-id'),
        );
        $this->assertNotNull(
            $this->app->make(AdminUserPasskeyRepositoryInterface::class)->findByCredentialId('credential-id'),
        );

        $this->assertSame(1, DB::table('refresh_tokens')->count());

        // コードは消費済みになる
        $this->assertCount(0, $this->app->make(RecoveryCodeRepositoryInterface::class)
            ->findUnusedByAdminUserIdForUpdate(new AdminUserId($adminUserId)));
        $this->assertSame(ConsumptionStatus::Consumed->value, (int)DB::table('admin_user_recovery_codes')->first()?->status);

        $log = $this->findAuditLog(AuditAction::RecoveryCodeUse, AuditTargetType::AdminUser, $adminUserId);
        $snapshot = $log['snapshot'];
        $this->assertIsArray($snapshot);
        $this->assertArrayHasKey('admin_user_passkey_id', $snapshot);
        $this->assertArrayHasKey('refresh_token_id', $snapshot);
    }

    #[Test]
    public function consumedCodeCannotBeReusedForSecondRecovery(): void
    {
        // 2 個以上のコードを発行し、コード A で復元成功 → 同じコード A での 2 回目は失敗し、
        // かつ未使用コード (A 以外) が過剰に消費されていないことを検証する
        $this->bindPasskeyAuthenticator();
        $adminUserId = $this->storeAdminUserWithPasskey('example@example.com');
        $codeAId = $this->storeRecoveryCode($adminUserId, 'A3KP-9QXR');
        $this->storeRecoveryCode($adminUserId, 'B7MN-2WYZ');

        // コード A で復元成功
        $authCeremonyId = $this->startRecovery('example@example.com', 'A3KP-9QXR');
        $this->postJson(route(AuthRouteMap::RecoveryFinish), [
            'authCeremonyId' => $authCeremonyId,
            'credential' => ['id' => 'new-credential-id'],
        ])->assertStatus(200);

        // 消費されたのは入力したコード A のみで、コード B は未使用のまま
        $this->assertSame(
            ConsumptionStatus::Consumed->value,
            (int)DB::table('admin_user_recovery_codes')->where('admin_user_recovery_code_id', $this->toBin($codeAId))->first()?->status,
        );
        $unusedCodes = $this->app->make(RecoveryCodeRepositoryInterface::class)
            ->findUnusedByAdminUserIdForUpdate(new AdminUserId($adminUserId));
        $this->assertCount(1, $unusedCodes);
        $this->assertNotSame($codeAId, $unusedCodes[0]->recoveryCodeId->value);

        // 同じコード A での 2 回目の復元は失敗する (start は列挙対策で 200 だが finish が失敗)
        $secondCeremonyId = $this->startRecovery('example@example.com', 'A3KP-9QXR');
        $this->postJson(route(AuthRouteMap::RecoveryFinish), [
            'authCeremonyId' => $secondCeremonyId,
            'credential' => ['id' => 'second-credential-id'],
        ])->assertStatus(401);

        $this->assertNull(
            $this->app->make(AdminUserPasskeyRepositoryInterface::class)->findByCredentialId('second-credential-id'),
        );
        $this->assertSame(1, DB::table('refresh_tokens')->count());

        // 2 回目失敗後もコード B は消費されていない
        $this->assertCount(1, $this->app->make(RecoveryCodeRepositoryInterface::class)
            ->findUnusedByAdminUserIdForUpdate(new AdminUserId($adminUserId)));
    }

    #[Test]
    public function startReturnsUniformCeremonyForUnknownEmail(): void
    {
        // ユーザー列挙を防ぐため、未登録メールでも登録済みと同一形状の 200 を返す
        $this->bindPasskeyAuthenticator();

        $this->postJson(route(AuthRouteMap::RecoveryStart), [
            'email' => 'unknown@example.com',
            'recoveryCode' => 'A3KP-9QXR',
            'name' => '新しいパスキー',
        ])->assertStatus(200)
            ->assertJson(
                static fn (AssertableJson $json) => $json->whereType('authCeremonyId', 'string')
                    ->where('publicKey.challenge', 'register-challenge')
                    ->etc(),
            );

        $this->assertSame(0, DB::table('refresh_tokens')->count());
    }

    #[Test]
    public function startReturnsUniformCeremonyForWrongCode(): void
    {
        // 実在ユーザー + 誤ったコードでも、未登録メールと区別できない 200 を返す
        $this->bindPasskeyAuthenticator();
        $adminUserId = $this->storeAdminUserWithPasskey('example@example.com');
        $this->storeRecoveryCode($adminUserId, 'A3KP-9QXR');

        $this->postJson(route(AuthRouteMap::RecoveryStart), [
            'email' => 'example@example.com',
            'recoveryCode' => 'WRONG-CODE',
            'name' => '新しいパスキー',
        ])->assertStatus(200)
            ->assertJson(
                static fn (AssertableJson $json) => $json->whereType('authCeremonyId', 'string')
                    ->where('publicKey.challenge', 'register-challenge')
                    ->etc(),
            );
    }

    #[Test]
    public function finishFailsForUnknownEmailCeremonyWithoutAuthenticating(): void
    {
        // ダミー ceremony では認証できない (列挙対策が認証バイパスを生まないこと)
        $this->bindPasskeyAuthenticator();

        $authCeremonyId = $this->startRecovery('unknown@example.com', 'A3KP-9QXR');

        $this->postJson(route(AuthRouteMap::RecoveryFinish), [
            'authCeremonyId' => $authCeremonyId,
            'credential' => ['id' => 'new-credential-id'],
        ])->assertStatus(401);

        $this->assertSame(0, DB::table('admin_user_passkeys')->count());
        $this->assertSame(0, DB::table('refresh_tokens')->count());
        $this->assertAuditLogCount(0);
    }

    #[Test]
    public function finishFailsForWrongCodeCeremonyWithoutAuthenticating(): void
    {
        $this->bindPasskeyAuthenticator();
        $adminUserId = $this->storeAdminUserWithPasskey('example@example.com');
        $this->storeRecoveryCode($adminUserId, 'A3KP-9QXR');

        $authCeremonyId = $this->startRecovery('example@example.com', 'WRONG-CODE');

        $this->postJson(route(AuthRouteMap::RecoveryFinish), [
            'authCeremonyId' => $authCeremonyId,
            'credential' => ['id' => 'new-credential-id'],
        ])->assertStatus(401);

        $this->assertSame(1, DB::table('admin_user_passkeys')->count());
        $this->assertSame(0, DB::table('refresh_tokens')->count());
        $this->assertAuditLogCount(0);
        $this->assertSame(ConsumptionStatus::Unused->value, (int)DB::table('admin_user_recovery_codes')->first()?->status);
    }

    #[Test]
    public function startIsRateLimitedPerEmail(): void
    {
        // レートリミッター用キャッシュが他テストから持ち越されないようにする
        $this->app->make('cache')->flush();
        config()->set('auth.passkey.rate_limit.recovery', 2);
        $this->bindPasskeyAuthenticator();

        foreach (range(1, 2) as $ignored) {
            $this->postJson(route(AuthRouteMap::RecoveryStart), [
                'email' => 'throttle@example.com',
                'recoveryCode' => 'A3KP-9QXR',
                'name' => '新しいパスキー',
            ])->assertStatus(200);
        }

        $this->postJson(route(AuthRouteMap::RecoveryStart), [
            'email' => 'throttle@example.com',
            'recoveryCode' => 'A3KP-9QXR',
            'name' => '新しいパスキー',
        ])->assertStatus(429);
    }

    private function startRecovery(string $email, string $recoveryCode): string
    {
        $response = $this->postJson(route(AuthRouteMap::RecoveryStart), [
            'email' => $email,
            'recoveryCode' => $recoveryCode,
            'name' => '新しいパスキー',
        ])->assertStatus(200);

        $authCeremonyId = $response->json('authCeremonyId');
        $this->assertIsString($authCeremonyId);

        return $authCeremonyId;
    }

    private function storeAdminUserWithPasskey(string $email, string $credentialId = 'credential-id'): string
    {
        $adminUserId = $this->generateUuid();
        $this->app->make(AdminUserRepository::class)->register(
            $this->createAdminUser($adminUserId, $email),
        );

        $this->app->make(AdminUserPasskeyRepositoryInterface::class)->save(new AdminUserPasskey(
            $this->generateUuid(),
            $adminUserId,
            'user-handle-' . $credentialId,
            'Primary passkey',
            $credentialId,
            'public-key',
            '00000000-0000-0000-0000-000000000000',
            ['internal'],
            true,
            false,
            123,
            new DateTimeImmutable('2026-01-01 00:00:00'),
            null,
        ));

        return $adminUserId;
    }

    private function storeRecoveryCode(string $adminUserId, string $plainCode): string
    {
        $hashedCode = $this->app->make(RecoveryCodeHasherInterface::class)->hash($plainCode);
        $recoveryCodeId = $this->generateUuid();

        $this->app->make(RecoveryCodeRepositoryInterface::class)->saveMany([
            new RecoveryCode(
                new RecoveryCodeId($recoveryCodeId),
                new AdminUserId($adminUserId),
                new HashedCodeValue($hashedCode),
                ConsumptionStatus::Unused,
                null,
            ),
        ]);

        return $recoveryCodeId;
    }

    private function toBin(string $uuid): string
    {
        return $this->app->make(UuidConverterInterface::class)->toBin($uuid);
    }

    private function bindPasskeyAuthenticator(bool $failFinish = false): void
    {
        $this->app->bind(PasskeyAuthenticatorInterface::class, static fn (): PasskeyAuthenticatorInterface => new class ($failFinish) implements PasskeyAuthenticatorInterface {
            public function __construct(private readonly bool $failFinish)
            {
            }

            public function startRegistration(string $userHandle, string $userName, string $displayName, array $excludePasskeys = []): PasskeyStartResult
            {
                return new PasskeyStartResult('{"challenge":"register-challenge"}', ['challenge' => 'register-challenge']);
            }

            public function finishRegistration(array $credential, string $optionsJson): PasskeyRegistrationResult
            {
                if ($this->failFinish) {
                    throw new RuntimeException('verification failed');
                }

                $id = $credential['id'] ?? 'new-credential-id';
                $credentialId = is_string($id) && $id !== '' ? $id : 'new-credential-id';

                return new PasskeyRegistrationResult($credentialId, 'public-key', 'new-user-handle', '00000000-0000-0000-0000-000000000000', ['internal'], true, false, 1);
            }

            public function startAuthentication(): PasskeyStartResult
            {
                throw new RuntimeException('unused');
            }

            public function credentialId(array $credential): ?string
            {
                throw new RuntimeException('unused');
            }

            public function finishAuthentication(array $credential, string $optionsJson, AdminUserPasskey $passkey, string $userHandle): PasskeyAuthenticationResult
            {
                throw new RuntimeException('unused');
            }
        });
    }
}
