<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Domain\Services\RecoveryCode;

use AdminUser\Domain\Models\AdminUserId;
use Auth\Domain\Models\RecoveryCode\ConsumptionStatus;
use Auth\Domain\Models\RecoveryCode\HashedCodeValue;
use Auth\Domain\Models\RecoveryCode\RecoveryCode;
use Auth\Domain\Models\RecoveryCode\RecoveryCodeId;
use Auth\Domain\Models\RecoveryCode\RecoveryCodeRepositoryInterface;
use Auth\Domain\Services\RecoveryCode\RecoveryCodeHasherInterface;
use Auth\Domain\Services\RecoveryCode\RecoveryCodeVerifyService;
use DateTimeImmutable;
use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RecoveryCodeVerifyServiceTest extends TestCase
{
    private const string ADMIN_USER_ID = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';

    private MockInterface&RecoveryCodeHasherInterface $hasher;

    private MockInterface&RecoveryCodeRepositoryInterface $repository;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->hasher = Mockery::mock(RecoveryCodeHasherInterface::class);
        $this->repository = Mockery::mock(RecoveryCodeRepositoryInterface::class);
    }

    #[Test]
    public function verifyReturnsMatchingUnusedCode(): void
    {
        $adminUserId = new AdminUserId(self::ADMIN_USER_ID);
        $code = $this->buildCode(ConsumptionStatus::Unused, null);

        $this->repository->shouldReceive('findUnusedByAdminUserIdForUpdate')
            ->with($adminUserId)
            ->andReturn([$code])
            ->once();

        $this->hasher->shouldReceive('verify')
            ->with('A3KP-9QXR', 'hashed-code')
            ->andReturn(true)
            ->once();

        $this->assertSame($code, $this->getInstance()->verify('A3KP-9QXR', $adminUserId));
    }

    #[Test]
    public function verifyReturnsNullWhenNoCodeMatches(): void
    {
        $adminUserId = new AdminUserId(self::ADMIN_USER_ID);
        $code = $this->buildCode(ConsumptionStatus::Unused, null);

        $this->repository->shouldReceive('findUnusedByAdminUserIdForUpdate')
            ->with($adminUserId)
            ->andReturn([$code])
            ->once();

        $this->hasher->shouldReceive('verify')
            ->with('WRONG-CODE', 'hashed-code')
            ->andReturn(false)
            ->once();

        $this->assertNull($this->getInstance()->verify('WRONG-CODE', $adminUserId));
    }

    #[Test]
    public function verifyReturnsNullWhenAdminUserHasNoCodes(): void
    {
        $adminUserId = new AdminUserId(self::ADMIN_USER_ID);

        $this->repository->shouldReceive('findUnusedByAdminUserIdForUpdate')
            ->with($adminUserId)
            ->andReturn([])
            ->once();

        $this->assertNull($this->getInstance()->verify('A3KP-9QXR', $adminUserId));
    }

    #[Test]
    public function verifyReturnsNullWhenMatchedCodeIsConsumed(): void
    {
        $adminUserId = new AdminUserId(self::ADMIN_USER_ID);
        $code = $this->buildCode(ConsumptionStatus::Consumed, new DateTimeImmutable('2026-01-01 00:00:00'));

        $this->repository->shouldReceive('findUnusedByAdminUserIdForUpdate')
            ->with($adminUserId)
            ->andReturn([$code])
            ->once();

        $this->hasher->shouldReceive('verify')
            ->with('A3KP-9QXR', 'hashed-code')
            ->andReturn(true)
            ->once();

        $this->assertNull($this->getInstance()->verify('A3KP-9QXR', $adminUserId));
    }

    private function buildCode(ConsumptionStatus $status, ?DateTimeImmutable $usedAt): RecoveryCode
    {
        return new RecoveryCode(
            new RecoveryCodeId('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'),
            new AdminUserId(self::ADMIN_USER_ID),
            new HashedCodeValue('hashed-code'),
            $status,
            $usedAt,
        );
    }

    private function getInstance(): RecoveryCodeVerifyService
    {
        return new RecoveryCodeVerifyService(
            $this->hasher,
            $this->repository,
        );
    }
}
