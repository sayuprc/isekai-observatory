<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Infrastructures\Token\AccessToken;

use Auth\Domain\Services\Token\AccessToken\AccessTokenPayload;
use Auth\Domain\Services\Token\AccessToken\JwtHandlerInterface;
use Auth\Infrastructures\Token\AccessToken\AccessTokenFactory;
use Carbon\Carbon;
use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AccessTokenFactoryTest extends TestCase
{
    private JwtHandlerInterface&MockInterface $jwt;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->jwt = Mockery::mock(JwtHandlerInterface::class);
    }

    #[Test]
    public function createSuccessfully(): void
    {
        Carbon::setTestNow('2019-12-09 10:30:00');

        $now = now()->toDateTimeImmutable();

        $payload = new AccessTokenPayload(
            iss: 'issuer',
            iat: $now->getTimestamp(),
            exp: $now->modify('+1 hours')->getTimestamp(),
            nbf: $now->getTimestamp(),
            jti: 'id',
        );

        $this->jwt->shouldReceive('generate')
            ->withArgs(static function (AccessTokenPayload $arg) use ($now): bool {
                $payload = $arg->toArray();

                return $payload['iss'] === 'issuer'
                    && $payload['iat'] === $now->getTimestamp()
                    && $payload['exp'] === $now->modify('+1 hours')->getTimestamp()
                    && $payload['nbf'] === $now->getTimestamp()
                    && $payload['jti'] === 'id';
            })
            ->andReturn('jwt')
            ->once();

        $this->getInstance()->create($payload);
    }

    private function getInstance(): AccessTokenFactory
    {
        return new AccessTokenFactory($this->jwt);
    }
}
