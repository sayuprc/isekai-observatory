<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Auth;

use AdminUser\Domain\Models\Email;
use AdminUser\Domain\Models\Permissions;
use AdminUser\Domain\Models\RegistrationToken\ConsumptionStatus;
use AdminUser\Domain\Models\RegistrationToken\ExpiredAt;
use AdminUser\Domain\Models\RegistrationToken\HashedTokenValue;
use AdminUser\Domain\Models\RegistrationToken\RegistrationToken;
use AdminUser\Domain\Models\RegistrationToken\RegistrationTokenId;
use AdminUser\Domain\Models\RegistrationToken\RegistrationTokenRepositoryInterface;
use AdminUser\Domain\Models\Role;
use AdminUser\Domain\Services\RegistrationToken\TokenHasherInterface;
use Auth\Domain\Models\AdminUserPasskey;
use Auth\Domain\Services\PasskeyAuthenticationResult;
use Auth\Domain\Services\PasskeyAuthenticatorInterface;
use Auth\Domain\Services\PasskeyRegistrationResult;
use Auth\Domain\Services\PasskeyStartResult;
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

class RegisterFinishTest extends DatabaseTestCase
{
    use AssertsAuditLog;

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
    public function canFinishRegistrationAndConsumeToken(): void
    {
        $this->bindPasskeyAuthenticator(false);
        $tokenId = $this->saveToken('plain-token', 'invitee@example.com');

        $authCeremonyId = $this->startRegistration();

        $this->postJson(route(AuthRouteMap::RegisterFinish), [
            'authCeremonyId' => $authCeremonyId,
            'token' => 'plain-token',
            'credential' => ['id' => 'credential-id'],
        ])->assertStatus(200)
            ->assertJson(
                static fn (AssertableJson $json) => $json->whereAllType([
                    'accessToken' => 'string',
                    'refreshTokenId' => 'string',
                    'refreshToken' => 'string',
                ])->etc(),
            );

        $tokenRow = DB::table('admin_user_registration_tokens')
            ->where('admin_user_registration_token_id', $this->app->make(UuidConverterInterface::class)->toBin($tokenId))
            ->first();
        $this->assertNotNull($tokenRow);
        $this->assertSame(ConsumptionStatus::Consumed->value, (int)$tokenRow->status);

        $userRow = DB::table('admin_users')->where('email', 'invitee@example.com')->first();
        $this->assertNotNull($userRow);
        $this->assertSame('新規ユーザー', $userRow->name);
        $this->assertArrayNotHasKey('password', (array)$userRow);

        $passkeyRow = DB::table('admin_user_passkeys')->first();
        $this->assertNotNull($passkeyRow);
        $this->assertSame('credential-id', $passkeyRow->credential_id);
        $this->assertSame('user-handle', $passkeyRow->user_handle);
        $this->assertSame('00000000-0000-0000-0000-000000000000', $passkeyRow->aaguid);
        $this->assertSame(['internal'], json_decode((string)$passkeyRow->transports, true));
        $this->assertSame(1, (int)$passkeyRow->backup_eligible);
        $this->assertSame(0, (int)$passkeyRow->backup_state);
        $this->assertSame(1, DB::table('refresh_tokens')->count());

        $converter = $this->app->make(UuidConverterInterface::class);
        $adminUserId = $converter->toUuid((string)$userRow->admin_user_id);
        $passkeyId = $converter->toUuid((string)$passkeyRow->admin_user_passkey_id);
        $refreshTokenRow = DB::table('refresh_tokens')->first();
        $this->assertNotNull($refreshTokenRow);
        $refreshTokenId = $converter->toUuid((string)$refreshTokenRow->refresh_token_id);

        $this->assertAuditLogCount(1);
        $log = $this->findAuditLog(AuditAction::Register, AuditTargetType::AdminUser, $adminUserId);
        $this->assertSame($adminUserId, $log['admin_user_id']);
        $snapshot = $log['snapshot'];
        $this->assertIsArray($snapshot);
        $this->assertSame($passkeyId, $snapshot['admin_user_passkey_id'] ?? null);
        $this->assertSame($refreshTokenId, $snapshot['refresh_token_id'] ?? null);
    }

    #[Test]
    public function failsWithoutDbChangesWhenPasskeyVerificationFails(): void
    {
        $this->bindPasskeyAuthenticator(true);
        $this->saveToken('plain-token', 'invitee@example.com');

        $authCeremonyId = $this->startRegistration();

        $this->postJson(route(AuthRouteMap::RegisterFinish), [
            'authCeremonyId' => $authCeremonyId,
            'token' => 'plain-token',
            'credential' => ['id' => 'credential-id'],
        ])->assertStatus(400);

        $this->assertSame(0, DB::table('admin_users')->count());
        $this->assertSame(0, DB::table('admin_user_passkeys')->count());
        $this->assertSame(0, DB::table('refresh_tokens')->count());
        $this->assertAuditLogCount(0);
        $token = DB::table('admin_user_registration_tokens')->first();
        $this->assertNotNull($token);
        $this->assertSame(ConsumptionStatus::Unused->value, (int)$token->status);
    }

    private function startRegistration(): string
    {
        $response = $this->postJson(route(AuthRouteMap::RegisterStart), [
            'token' => 'plain-token',
            'email' => 'invitee@example.com',
            'name' => '新規ユーザー',
        ])->assertStatus(200);

        $authCeremonyId = $response->json('authCeremonyId');
        $this->assertIsString($authCeremonyId);

        return $authCeremonyId;
    }

    private function bindPasskeyAuthenticator(bool $failFinish): void
    {
        $this->app->bind(PasskeyAuthenticatorInterface::class, static fn (): PasskeyAuthenticatorInterface => new class ($failFinish) implements PasskeyAuthenticatorInterface {
            public function __construct(private readonly bool $failFinish)
            {
            }

            public function startRegistration(string $userHandle, string $userName, string $displayName, array $excludePasskeys = []): PasskeyStartResult
            {
                return new PasskeyStartResult('{"challenge":"challenge"}', ['challenge' => 'challenge']);
            }

            public function finishRegistration(array $credential, string $optionsJson): PasskeyRegistrationResult
            {
                if ($this->failFinish) {
                    throw new RuntimeException('verification failed');
                }

                return new PasskeyRegistrationResult('credential-id', 'public-key', 'user-handle', '00000000-0000-0000-0000-000000000000', ['internal'], true, false, 123);
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

    private function saveToken(string $plainToken, string $email): string
    {
        $id = $this->generateUuid();
        $hashedToken = $this->app->make(TokenHasherInterface::class)->hash($plainToken);

        $this->app->make(RegistrationTokenRepositoryInterface::class)->save(new RegistrationToken(
            new RegistrationTokenId($id),
            new HashedTokenValue($hashedToken),
            new Email($email),
            Role::General,
            Permissions::reconstruct([]),
            new ExpiredAt(new DateTimeImmutable('+7 days')),
            ConsumptionStatus::Unused,
        ));

        return $id;
    }
}
