<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Domain\Services\Token\RefreshToken;

use Auth\Domain\Models\Token\RefreshToken\ConsumptionStatus;
use Auth\Domain\Services\Token\RefreshToken\RandomTokenGeneratorInterface;
use Auth\Domain\Services\Token\RefreshToken\RefreshTokenIssueService;
use Auth\Domain\Services\Token\RefreshToken\TokenHasherInterface;
use DateTimeImmutable;
use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\ClockInterface;
use Support\Contracts\Uuid\UuidGeneratorInterface;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class RefreshTokenIssueServiceTest extends TestCase
{
    use EntityFactory;

    private ClockInterface&MockInterface $clock;

    private MockInterface&UuidGeneratorInterface $uuidGenerator;

    private MockInterface&RandomTokenGeneratorInterface $randomTokenGenerator;

    private MockInterface&TokenHasherInterface $tokenHasher;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->clock = Mockery::mock(ClockInterface::class);
        $this->uuidGenerator = Mockery::mock(UuidGeneratorInterface::class);
        $this->randomTokenGenerator = Mockery::mock(RandomTokenGeneratorInterface::class);
        $this->tokenHasher = Mockery::mock(TokenHasherInterface::class);
    }

    #[Test]
    public function issue(): void
    {
        $adminUserId = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $generatedUuid = 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB';
        $generatedToken = 'random-token-value-12345678901234567890123456789012';
        $hashedToken = 'hashed-token-value';
        $now = new DateTimeImmutable('2026-02-01 00:00:00');

        $this->clock->shouldReceive('now')
            ->with()
            ->andReturn($now)
            ->once();

        $this->uuidGenerator->shouldReceive('generate')
            ->with()
            ->andReturn($generatedUuid)
            ->once();

        $this->randomTokenGenerator->shouldReceive('generate')
            ->with()
            ->andReturn($generatedToken)
            ->once();

        $this->tokenHasher->shouldReceive('hash')
            ->with($generatedToken)
            ->andReturn($hashedToken)
            ->once();

        $expectedExpiredAt = $now->modify('+7 days');

        $expectedRefreshToken = $this->createRefreshToken(
            $generatedUuid,
            $adminUserId,
            $hashedToken,
            $expectedExpiredAt,
            ConsumptionStatus::Unused,
        );

        $issued = $this->getInstance()->issue($adminUserId);

        $this->assertArrayHasKey('token', $issued);
        $this->assertArrayHasKey('plainToken', $issued);
        $this->assertEquals($expectedRefreshToken, $issued['token']);
        $this->assertSame($generatedToken, $issued['plainToken']);
    }

    private function getInstance(): RefreshTokenIssueService
    {
        return new RefreshTokenIssueService(
            $this->clock,
            $this->uuidGenerator,
            $this->randomTokenGenerator,
            $this->tokenHasher,
        );
    }
}
