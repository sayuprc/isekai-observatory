<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Domain\Services\Token\AccessToken;

use Auth\Domain\Models\Token\AccessToken\AccessTokenFactoryInterface;
use Auth\Domain\Services\Token\AccessToken\AccessTokenIssueService;
use Auth\Domain\Services\Token\AccessToken\AccessTokenPayload;
use Auth\Domain\Services\Token\AccessToken\JwtConfig;
use DateTimeImmutable;
use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\ClockInterface;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class AccessTokenIssueServiceTest extends TestCase
{
    use EntityFactory;

    private ClockInterface&MockInterface $clock;

    private AccessTokenFactoryInterface&MockInterface $factory;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->clock = Mockery::mock(ClockInterface::class);
        $this->factory = Mockery::mock(AccessTokenFactoryInterface::class);
    }

    #[Test]
    public function issue(): void
    {
        $id = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $issuer = 'test-issuer';
        $now = new DateTimeImmutable('2026-02-01 00:00:00');

        $this->clock->shouldReceive('now')
            ->with()
            ->andReturn($now)
            ->once();

        $expectedAccessToken = $this->createAccessToken('jwt-token');

        $this->factory->shouldReceive('create')
            ->withArgs(
                static fn (AccessTokenPayload $payload): bool => $payload->iss === $issuer
                    && $payload->iat === $now->getTimestamp()
                    && $payload->exp === $now->modify('+1 hours')->getTimestamp()
                    && $payload->nbf === $now->getTimestamp()
                    && $payload->jti === $id,
            )
            ->andReturn($expectedAccessToken)
            ->once();

        $actual = $this->getInstance(new JwtConfig('', '', $issuer))->issue($id);

        $this->assertSame($expectedAccessToken, $actual);
    }

    private function getInstance(JwtConfig $config): AccessTokenIssueService
    {
        return new AccessTokenIssueService(
            $this->clock,
            $config,
            $this->factory,
        );
    }
}
