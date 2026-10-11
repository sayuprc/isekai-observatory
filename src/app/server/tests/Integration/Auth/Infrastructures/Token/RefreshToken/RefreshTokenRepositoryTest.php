<?php

declare(strict_types=1);

namespace Tests\Integration\Auth\Infrastructures\Token\RefreshToken;

use AdminUser\Domain\Models\Role;
use AdminUser\Infrastructures\AdminUserRepository;
use Auth\Domain\Models\Token\RefreshToken\ConsumptionStatus;
use Auth\Domain\Models\Token\RefreshToken\RefreshTokenId;
use Auth\Domain\Services\Token\RefreshToken\TokenHasherInterface;
use Auth\Infrastructures\Token\RefreshToken\RefreshTokenRepository;
use Carbon\Carbon;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\Uuid\UuidConverterInterface;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class RefreshTokenRepositoryTest extends DatabaseTestCase
{
    use EntityFactory;

    #[Test]
    public function findActive(): void
    {
        Carbon::setTestNow('2026-01-01 00:00:00');

        $adminUser = $this->createAdminUser($this->generateUuid(), 'user@example.com', Role::General, [], new DateTimeImmutable());
        $this->app->make(AdminUserRepository::class)->register($adminUser);

        $refreshToken = $this->createRefreshToken(
            $this->generateUuid(),
            $adminUser->adminUserId->value,
            'token-value',
            now()->addMinutes(30)->toDateTimeImmutable(),
            ConsumptionStatus::Unused,
        );

        $repository = $this->getInstance();
        $repository->save($refreshToken);

        $found = $repository->findActive($refreshToken->refreshTokenId);

        $this->assertNotNull($found);
        $this->assertEquals($refreshToken, $found);
    }

    #[Test]
    public function findActiveNotFound(): void
    {
        $found = $this->getInstance()->findActive(new RefreshTokenId($this->generateUuid()));

        $this->assertNull($found);
    }

    #[Test]
    public function findActiveExpiredReturnsNull(): void
    {
        Carbon::setTestNow('2026-01-01 00:00:00');

        $adminUser = $this->createAdminUser($this->generateUuid(), 'user@example.com', Role::General, [], new DateTimeImmutable());
        $this->app->make(AdminUserRepository::class)->register($adminUser);

        $refreshToken = $this->createRefreshToken(
            $this->generateUuid(),
            $adminUser->adminUserId->value,
            'token-value',
            now()->subMinutes(1)->toDateTimeImmutable(),
            ConsumptionStatus::Unused,
        );

        $repository = $this->getInstance();
        $repository->save($refreshToken);

        $found = $repository->findActive($refreshToken->refreshTokenId);

        $this->assertNull($found);
    }

    #[Test]
    public function findActiveConsumedReturnsNull(): void
    {
        Carbon::setTestNow('2026-01-01 00:00:00');

        $adminUser = $this->createAdminUser($this->generateUuid(), 'user@example.com', Role::General, [], new DateTimeImmutable());
        $this->app->make(AdminUserRepository::class)->register($adminUser);

        $refreshToken = $this->createRefreshToken(
            $this->generateUuid(),
            $adminUser->adminUserId->value,
            'token-value',
            now()->addMinutes(30)->toDateTimeImmutable(),
            ConsumptionStatus::Consumed,
        );

        $repository = $this->getInstance();
        $repository->save($refreshToken);

        $found = $repository->findActive($refreshToken->refreshTokenId);

        $this->assertNull($found);
    }

    #[Test]
    public function save(): void
    {
        Carbon::setTestNow('2026-01-01 00:00:00');

        $adminUser = $this->createAdminUser($this->generateUuid(), 'user@example.com', Role::General, [], new DateTimeImmutable());
        $this->app->make(AdminUserRepository::class)->register($adminUser);

        $refreshToken = $this->createRefreshToken(
            $this->generateUuid(),
            $adminUser->adminUserId->value,
            'token-value',
            now()->addMinutes(30)->toDateTimeImmutable(),
            ConsumptionStatus::Unused,
        );

        $repository = $this->getInstance();
        $repository->save($refreshToken);

        $found = $repository->findActive($refreshToken->refreshTokenId);

        $this->assertNotNull($found);
        $this->assertEquals($refreshToken, $found);
    }

    #[Test]
    public function saveUpdatesExisting(): void
    {
        Carbon::setTestNow('2026-01-01 00:00:00');

        $adminUser = $this->createAdminUser($this->generateUuid(), 'user@example.com', Role::General, [], new DateTimeImmutable());
        $this->app->make(AdminUserRepository::class)->register($adminUser);

        $refreshToken = $this->createRefreshToken(
            $this->generateUuid(),
            $adminUser->adminUserId->value,
            'token-value',
            now()->addMinutes(30)->toDateTimeImmutable(),
            ConsumptionStatus::Unused,
        );

        $repository = $this->getInstance();
        $repository->save($refreshToken);

        $consumed = $this->createRefreshToken(
            $refreshToken->refreshTokenId->value,
            $adminUser->adminUserId->value,
            'token-value',
            now()->addMinutes(30)->toDateTimeImmutable(),
            ConsumptionStatus::Consumed,
        );

        $repository->save($consumed);

        $found = $repository->findActive($refreshToken->refreshTokenId);

        $this->assertNull($found);
    }

    #[Test]
    public function saveStoresHashedTokenDirectly(): void
    {
        Carbon::setTestNow('2026-01-01 00:00:00');

        $adminUser = $this->createAdminUser($this->generateUuid(), 'user@example.com', Role::General, [], new DateTimeImmutable());
        $this->app->make(AdminUserRepository::class)->register($adminUser);

        $hasher = $this->app->make(TokenHasherInterface::class);
        $plainToken = 'my-plain-token-value-12345';
        $hashedToken = $hasher->hash($plainToken);

        $refreshToken = $this->createRefreshToken(
            $this->generateUuid(),
            $adminUser->adminUserId->value,
            $hashedToken,
            now()->addMinutes(30)->toDateTimeImmutable(),
            ConsumptionStatus::Unused,
        );

        $repository = $this->getInstance();
        $repository->save($refreshToken);

        $converter = $this->app->make(UuidConverterInterface::class);
        $stored = DB::table('refresh_tokens')
            ->where('refresh_token_id', $converter->toBin($refreshToken->refreshTokenId->value))
            ->first();

        $this->assertNotNull($stored);
        $this->assertEquals($hashedToken, $stored->token);
        $this->assertTrue($hasher->verify($plainToken, $stored->token));
    }

    private function getInstance(): RefreshTokenRepository
    {
        return $this->app->make(RefreshTokenRepository::class);
    }
}
