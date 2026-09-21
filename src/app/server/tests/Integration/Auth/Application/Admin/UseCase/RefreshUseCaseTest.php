<?php

declare(strict_types=1);

namespace Tests\Integration\Auth\Application\Admin\UseCase;

use AdminUser\Infrastructures\AdminUserRepository;
use Auth\Application\Admin\UseCase\Refresh\RefreshInputData;
use Auth\Application\Admin\UseCase\Refresh\RefreshUseCase;
use Auth\Domain\Models\Token\RefreshToken\ConsumptionStatus;
use Auth\Domain\Services\Token\RefreshToken\TokenHasherInterface;
use Carbon\Carbon;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditTargetType;
use Tests\Support\Concerns\AssertsAuditLog;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class RefreshUseCaseTest extends DatabaseTestCase
{
    use AssertsAuditLog;
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function recordsAuditLogOnRefresh(): void
    {
        Carbon::setTestNow('2026-01-01 00:00:00');

        config()->set([
            'auth.jwt.alg' => 'HS256',
            'auth.jwt.key' => str_repeat('k', 256),
        ]);

        $adminUser = $this->createAdminUser($this->generateUuid(), 'user@example.com');
        $this->app->make(AdminUserRepository::class)->register($adminUser);

        $plainToken = 'plain-refresh-token';
        $hashedToken = $this->app->make(TokenHasherInterface::class)->hash($plainToken);

        $refreshToken = $this->createRefreshToken(
            $this->generateUuid(),
            $adminUser->adminUserId->value,
            $hashedToken,
            new DateTimeImmutable('2026-01-01 00:30:00'),
            ConsumptionStatus::Unused,
        );
        $this->storeRefreshTokens($refreshToken);

        $this->app->make(RefreshUseCase::class)
            ->handle(new RefreshInputData($refreshToken->refreshTokenId->value, $plainToken));

        $this->assertAuditLogCount(1);
        $log = $this->findAuditLog(AuditAction::Refresh, AuditTargetType::AdminUser, $adminUser->adminUserId->value);
        $this->assertSame($adminUser->adminUserId->value, $log['admin_user_id']);
        $this->assertArrayHasKey('refresh_token_id', $log['snapshot']);
        $this->assertArrayNotHasKey('refresh_token', $log['snapshot']);
        $this->assertArrayNotHasKey('plain_token', $log['snapshot']);
        $this->assertNotContains($plainToken, $log['snapshot'], 'snapshot に平文トークンが含まれてはならない');
        $this->assertNotContains($hashedToken, $log['snapshot'], 'snapshot にハッシュ済みトークンが含まれてはならない');
    }
}
