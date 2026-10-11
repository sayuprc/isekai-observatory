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
use AdminUser\Infrastructures\AdminUserRepository;
use Auth\Domain\Models\AdminUserPasskey;
use Auth\Domain\Services\PasskeyAuthenticationResult;
use Auth\Domain\Services\PasskeyAuthenticatorInterface;
use Auth\Domain\Services\PasskeyRegistrationResult;
use Auth\Domain\Services\PasskeyStartResult;
use Auth\Route\AuthRouteMap;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class RegisterStartTest extends DatabaseTestCase
{
    use EntityFactory;

    #[Test]
    public function canStartRegistration(): void
    {
        $this->bindPasskeyAuthenticator();
        $this->saveToken('plain-token', 'invitee@example.com');

        $this->postJson(route(AuthRouteMap::RegisterStart), [
            'token' => 'plain-token',
            'email' => 'invitee@example.com',
            'name' => '新規ユーザー',
        ])->assertStatus(200)
            ->assertJson(
                static fn (AssertableJson $json) => $json->whereType('authCeremonyId', 'string')
                    ->where('publicKey.challenge', 'challenge')
                    ->etc(),
            );

        $this->assertSame(0, DB::table('admin_users')->count());
        $token = DB::table('admin_user_registration_tokens')->first();
        $this->assertNotNull($token);
        $this->assertSame(ConsumptionStatus::Unused->value, (int)$token->status);
    }

    #[Test]
    public function failsWithUnknownToken(): void
    {
        $this->bindPasskeyAuthenticator();
        $this->saveToken('plain-token', 'invitee@example.com');

        $this->postJson(route(AuthRouteMap::RegisterStart), [
            'token' => 'wrong-token',
            'email' => 'invitee@example.com',
            'name' => '新規ユーザー',
        ])->assertStatus(400);

        $this->assertSame(0, DB::table('admin_users')->count());
        $token = DB::table('admin_user_registration_tokens')->first();
        $this->assertNotNull($token);
        $this->assertSame(ConsumptionStatus::Unused->value, (int)$token->status);
    }

    #[Test]
    public function failsWithEmailCollision(): void
    {
        $this->bindPasskeyAuthenticator();
        $this->app->make(AdminUserRepository::class)->register(
            $this->createAdminUser($this->generateUuid(), 'invitee@example.com'),
        );
        $this->saveToken('plain-token', 'invitee@example.com');

        $this->postJson(route(AuthRouteMap::RegisterStart), [
            'token' => 'plain-token',
            'email' => 'invitee@example.com',
            'name' => '新規ユーザー',
        ])->assertStatus(400);

        $this->assertSame(1, DB::table('admin_users')->count());
        $token = DB::table('admin_user_registration_tokens')->first();
        $this->assertNotNull($token);
        $this->assertSame(ConsumptionStatus::Unused->value, (int)$token->status);
    }

    #[Test]
    public function failsWithValidationViolation(): void
    {
        $this->bindPasskeyAuthenticator();
        $this->saveToken('plain-token', 'invitee@example.com');

        $this->postJson(route(AuthRouteMap::RegisterStart), [
            'email' => 'invitee@example.com',
            'name' => '新規ユーザー',
        ])->assertStatus(422);

        $this->assertSame(0, DB::table('admin_users')->count());
        $token = DB::table('admin_user_registration_tokens')->first();
        $this->assertNotNull($token);
        $this->assertSame(ConsumptionStatus::Unused->value, (int)$token->status);
    }

    private function bindPasskeyAuthenticator(): void
    {
        $this->app->bind(PasskeyAuthenticatorInterface::class, static fn (): PasskeyAuthenticatorInterface => new class () implements PasskeyAuthenticatorInterface {
            public function startRegistration(string $userHandle, string $userName, string $displayName, array $excludePasskeys = []): PasskeyStartResult
            {
                return new PasskeyStartResult('{"challenge":"challenge"}', ['challenge' => 'challenge']);
            }

            public function finishRegistration(array $credential, string $optionsJson): PasskeyRegistrationResult
            {
                throw new RuntimeException('unused');
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

    private function saveToken(string $plainToken, string $email): void
    {
        $hashedToken = $this->app->make(TokenHasherInterface::class)->hash($plainToken);

        $this->app->make(RegistrationTokenRepositoryInterface::class)->save(new RegistrationToken(
            new RegistrationTokenId($this->generateUuid()),
            new HashedTokenValue($hashedToken),
            new Email($email),
            Role::General,
            Permissions::reconstruct([]),
            new ExpiredAt(new DateTimeImmutable('+7 days')),
            ConsumptionStatus::Unused,
        ));
    }
}
