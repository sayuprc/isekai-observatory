<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Domain\Services\RecoveryCode;

use AdminUser\Domain\Models\AdminUserId;
use Auth\Domain\Models\RecoveryCode\ConsumptionStatus;
use Auth\Domain\Models\RecoveryCode\RecoveryCode;
use Auth\Domain\Services\RecoveryCode\RandomRecoveryCodeGeneratorInterface;
use Auth\Domain\Services\RecoveryCode\RecoveryCodeHasherInterface;
use Auth\Domain\Services\RecoveryCode\RecoveryCodeIssueService;
use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\Uuid\UuidGeneratorInterface;
use Tests\TestCase;

class RecoveryCodeIssueServiceTest extends TestCase
{
    private const string ADMIN_USER_ID = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';

    private MockInterface&UuidGeneratorInterface $uuidGenerator;

    private MockInterface&RandomRecoveryCodeGeneratorInterface $randomGenerator;

    private MockInterface&RecoveryCodeHasherInterface $hasher;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->uuidGenerator = Mockery::mock(UuidGeneratorInterface::class);
        $this->randomGenerator = Mockery::mock(RandomRecoveryCodeGeneratorInterface::class);
        $this->hasher = Mockery::mock(RecoveryCodeHasherInterface::class);
    }

    #[Test]
    public function issueGeneratesTenCodes(): void
    {
        $this->uuidGenerator->shouldReceive('generate')
            ->andReturnUsing(static fn (): string => sprintf('%08d-0000-0000-0000-000000000000', mt_rand(0, 99999999)))
            ->times(10);

        $this->randomGenerator->shouldReceive('generate')
            ->andReturn('A3KP-9QXR')
            ->times(10);

        $this->hasher->shouldReceive('hash')
            ->with('A3KP-9QXR')
            ->andReturn('hashed-code')
            ->times(10);

        ['codes' => $codes, 'plainCodes' => $plainCodes] = $this->getInstance()->issue(new AdminUserId(self::ADMIN_USER_ID));

        $this->assertCount(10, $codes);
        $this->assertCount(10, $plainCodes);
    }

    #[Test]
    public function issueReturnsHashedCodesAndMatchingPlainCodes(): void
    {
        $this->uuidGenerator->shouldReceive('generate')
            ->andReturnUsing(static fn (): string => sprintf('%08d-0000-0000-0000-000000000000', mt_rand(0, 99999999)))
            ->times(10);

        $this->randomGenerator->shouldReceive('generate')
            ->andReturn('A3KP-9QXR')
            ->times(10);

        $this->hasher->shouldReceive('hash')
            ->with('A3KP-9QXR')
            ->andReturn('hashed-code')
            ->times(10);

        ['codes' => $codes, 'plainCodes' => $plainCodes] = $this->getInstance()->issue(new AdminUserId(self::ADMIN_USER_ID));

        foreach ($codes as $code) {
            $this->assertInstanceOf(RecoveryCode::class, $code);
            $this->assertSame('hashed-code', $code->code->value);
            $this->assertSame(self::ADMIN_USER_ID, $code->adminUserId->value);
            $this->assertSame(ConsumptionStatus::Unused, $code->status);
            $this->assertNull($code->usedAt);
        }

        foreach ($plainCodes as $plainCode) {
            $this->assertSame('A3KP-9QXR', $plainCode);
        }
    }

    private function getInstance(): RecoveryCodeIssueService
    {
        return new RecoveryCodeIssueService(
            $this->uuidGenerator,
            $this->randomGenerator,
            $this->hasher,
        );
    }
}
