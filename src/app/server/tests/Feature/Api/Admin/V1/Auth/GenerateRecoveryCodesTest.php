<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Auth;

use AdminUser\Domain\Models\AdminUser;
use AdminUser\Domain\Models\Role;
use AdminUser\Infrastructures\AdminUserRepository;
use Auth\Domain\Services\RecoveryCode\RecoveryCodeHasherInterface;
use Auth\Domain\Services\Token\AccessToken\AccessTokenIssueService;
use Auth\Domain\Services\Token\RefreshToken\RefreshTokenIssueService;
use Auth\Infrastructures\Token\RefreshToken\RefreshTokenRepository;
use Auth\Route\AuthRouteMap;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\Fluent\AssertableJson;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditTargetType;
use Tests\Support\Concerns\AssertsAuditLog;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class GenerateRecoveryCodesTest extends DatabaseTestCase
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
    public function generatesPlainCodesAndStoresHashesOnly(): void
    {
        $user = $this->authenticate();

        $response = $this->postJson(route(AuthRouteMap::GenerateRecoveryCodes))
            ->assertStatus(200)
            ->assertJson(
                static fn (AssertableJson $json) => $json->has('recoveryCodes', 10)->etc(),
            );

        /** @var list<string> $plainCodes */
        $plainCodes = $response->json('recoveryCodes');

        $this->assertCount(10, DB::table('admin_user_recovery_codes')->get());

        $hasher = $this->app->make(RecoveryCodeHasherInterface::class);
        $storedCodes = DB::table('admin_user_recovery_codes')->pluck('code')->all();

        foreach ($plainCodes as $plainCode) {
            $this->assertNotContains($plainCode, $storedCodes);
        }

        foreach ($plainCodes as $plainCode) {
            $matched = array_filter($storedCodes, static fn (string $stored): bool => $hasher->verify($plainCode, $stored));
            $this->assertCount(1, $matched);
        }

        $log = $this->findAuditLog(AuditAction::RecoveryCodeIssue, AuditTargetType::AdminUser, $user->adminUserId->value);
        $snapshot = $log['snapshot'];
        $this->assertIsArray($snapshot);
        $this->assertSame(10, $snapshot['count'] ?? null);
    }

    #[Test]
    public function regeneratesAndReplacesExistingCodes(): void
    {
        $this->authenticate();

        $first = $this->postJson(route(AuthRouteMap::GenerateRecoveryCodes))->assertStatus(200);
        /** @var list<string> $firstCodes */
        $firstCodes = $first->json('recoveryCodes');

        $this->postJson(route(AuthRouteMap::GenerateRecoveryCodes))->assertStatus(200);

        $this->assertSame(10, DB::table('admin_user_recovery_codes')->count());

        $hasher = $this->app->make(RecoveryCodeHasherInterface::class);
        $storedCodes = DB::table('admin_user_recovery_codes')->pluck('code')->all();

        foreach ($firstCodes as $oldCode) {
            $matched = array_filter($storedCodes, static fn (string $stored): bool => $hasher->verify($oldCode, $stored));
            $this->assertCount(0, $matched);
        }
    }

    #[Test]
    public function requiresAuthentication(): void
    {
        $this->postJson(route(AuthRouteMap::GenerateRecoveryCodes))->assertStatus(401);

        $this->assertSame(0, DB::table('admin_user_recovery_codes')->count());
    }

    private function authenticate(): AdminUser
    {
        $user = $this->createAdminUser($this->generateUuid(), 'root@example.com', Role::Privilege);

        $refreshToken = $this->app->make(RefreshTokenIssueService::class)->issue($user->adminUserId->value)['token'];

        $this->app->make(AdminUserRepository::class)->register($user);
        $this->app->make(RefreshTokenRepository::class)->save($refreshToken);

        $accessToken = $this->app->make(AccessTokenIssueService::class)->issue($refreshToken->refreshTokenId->value);

        $this->withHeader('Authorization', 'Bearer ' . $accessToken->jwt->value);

        return $user;
    }
}
