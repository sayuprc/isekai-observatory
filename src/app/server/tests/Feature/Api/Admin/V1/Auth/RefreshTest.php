<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Auth;

use AdminUser\Infrastructures\AdminUserRepository;
use Auth\Domain\Models\Token\RefreshToken\ConsumptionStatus;
use Auth\Domain\Models\Token\RefreshToken\RefreshToken;
use Auth\Domain\Models\Token\RefreshToken\RefreshTokenRepositoryInterface;
use Auth\Domain\Services\Token\RefreshToken\TokenHasherInterface;
use Auth\Route\AuthRouteMap;
use Carbon\Carbon;
use DateTimeImmutable;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class RefreshTest extends DatabaseTestCase
{
    use EntityFactory;

    #[Test]
    public function canRefreshAccessToken(): void
    {
        Carbon::setTestNow('2026-01-01 00:00:00');

        config()->set([
            'auth.jwt.alg' => 'HS256',
            'auth.jwt.key' => str_repeat('k', 256),
        ]);

        $plainToken = 'plain-refresh-token';
        $refreshToken = $this->storeRefreshToken($plainToken, now()->addMinutes(30)->toDateTimeImmutable());

        $this->postJson(route(AuthRouteMap::Refresh), [
            'refreshTokenId' => $refreshToken->refreshTokenId->value,
            'refreshToken' => $plainToken,
        ])->assertStatus(200)
            ->assertJson(
                static fn (AssertableJson $json) => $json->has('accessToken')
                    ->has('refreshTokenId')
                    ->has('refreshToken')
                    ->whereType('accessToken', 'string')
                    ->whereType('refreshTokenId', 'string')
                    ->whereType('refreshToken', 'string')
                    ->etc(),
            );
    }

    #[Test]
    public function cannotReuseRefreshTokenAfterRefresh(): void
    {
        Carbon::setTestNow('2026-01-01 00:00:00');

        config()->set([
            'auth.jwt.alg' => 'HS256',
            'auth.jwt.key' => str_repeat('k', 256),
        ]);

        $plainToken = 'plain-refresh-token';
        $refreshToken = $this->storeRefreshToken($plainToken, now()->addMinutes(30)->toDateTimeImmutable());

        $this->postJson(route(AuthRouteMap::Refresh), [
            'refreshTokenId' => $refreshToken->refreshTokenId->value,
            'refreshToken' => $plainToken,
        ])->assertStatus(200);

        $this->postJson(route(AuthRouteMap::Refresh), [
            'refreshTokenId' => $refreshToken->refreshTokenId->value,
            'refreshToken' => $plainToken,
        ])->assertStatus(401);
    }

    #[Test]
    public function unauthorizedWhenSecretDoesNotMatch(): void
    {
        Carbon::setTestNow('2026-01-01 00:00:00');

        config()->set([
            'auth.jwt.alg' => 'HS256',
            'auth.jwt.key' => str_repeat('k', 256),
        ]);

        $plainToken = 'plain-refresh-token';
        $refreshToken = $this->storeRefreshToken($plainToken, now()->addMinutes(30)->toDateTimeImmutable());

        $this->postJson(route(AuthRouteMap::Refresh), [
            'refreshTokenId' => $refreshToken->refreshTokenId->value,
            'refreshToken' => 'different-token',
        ])->assertStatus(401);
    }

    #[Test]
    public function unauthorizedWhenRefreshTokenExpired(): void
    {
        Carbon::setTestNow('2026-01-01 00:00:00');

        config()->set([
            'auth.jwt.alg' => 'HS256',
            'auth.jwt.key' => str_repeat('k', 256),
        ]);

        $plainToken = 'plain-refresh-token';
        $refreshToken = $this->storeRefreshToken($plainToken, now()->subMinute()->toDateTimeImmutable());

        $this->postJson(route(AuthRouteMap::Refresh), [
            'refreshTokenId' => $refreshToken->refreshTokenId->value,
            'refreshToken' => $plainToken,
        ])->assertStatus(401);
    }

    private function storeRefreshToken(string $plainToken, DateTimeImmutable $expiredAt): RefreshToken
    {
        $user = $this->createAdminUser($this->generateUuid(), 'example@example.com');
        $this->app->make(AdminUserRepository::class)->register($user);

        $hashedToken = $this->app->make(TokenHasherInterface::class)->hash($plainToken);
        $refreshToken = $this->createRefreshToken(
            $this->generateUuid(),
            $user->adminUserId->value,
            $hashedToken,
            $expiredAt,
            ConsumptionStatus::Unused,
        );

        $this->app->make(RefreshTokenRepositoryInterface::class)->save($refreshToken);

        return $refreshToken;
    }
}
