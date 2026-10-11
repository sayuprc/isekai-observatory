<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Infrastructures\Token\AccessToken;

use Auth\Domain\Services\Token\AccessToken\AccessTokenPayload;
use Auth\Domain\Services\Token\AccessToken\JwtConfig;
use Auth\Infrastructures\Token\AccessToken\JwtHandler;
use DateTimeImmutable;
use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\ClockInterface;
use Support\Contracts\MapperInterface;
use Tests\TestCase;

class JwtHandlerTest extends TestCase
{
    private ClockInterface&MockInterface $clock;

    private MapperInterface&MockInterface $mapper;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->clock = Mockery::mock(ClockInterface::class);
        $this->mapper = Mockery::mock(MapperInterface::class);
    }

    #[Test]
    public function generateJwtSuccessfully(): void
    {
        $jwt = $this->getInstance(new JwtConfig('HS256', str_repeat('k', 256), 'iss'))->generate(new AccessTokenPayload(
            iss: 'iss',
            iat: 0,
            exp: 180,
            nbf: 0,
            jti: 'jti',
        ));

        $elements = explode('.', $jwt);
        $payload = json_decode(base64_decode($elements[1]), true);

        $this->assertSame([
            'iss' => 'iss',
            'iat' => 0,
            'exp' => 180,
            'nbf' => 0,
            'jti' => 'jti',
        ], $payload);
    }

    #[Test]
    public function verifyJwtSuccessfully(): void
    {
        $now = new DateTimeImmutable();
        $afterAHour = $now->modify('+1 hours');

        $handler = $this->getInstance(new JwtConfig('HS256', str_repeat('k', 256), 'iss'));

        $jwt = $handler->generate(new AccessTokenPayload(
            iss: 'iss',
            iat: $now->getTimestamp(),
            exp: $afterAHour->getTimestamp(),
            nbf: $now->getTimestamp(),
            jti: 'jti',
        ));

        $this->clock->shouldReceive('now')
            ->with()
            ->andReturn($now)
            ->once();

        $this->mapper->shouldReceive('map')
            ->withArgs(static fn (string $class, mixed $_) => $class === AccessTokenPayload::class)
            ->andReturn(new AccessTokenPayload(
                'iss',
                $now->getTimestamp(),
                $afterAHour->getTimestamp(),
                $now->getTimestamp(),
                'jti',
            ))
            ->once();

        $payload = $handler->verify($jwt);

        $this->assertNotNull($payload);
        $this->assertSame('iss', $payload->iss);
        $this->assertSame($now->getTimestamp(), $payload->iat);
        $this->assertSame($afterAHour->getTimestamp(), $payload->exp);
        $this->assertSame($now->getTimestamp(), $payload->nbf);
        $this->assertSame('jti', $payload->jti);
    }

    #[Test]
    public function returnsNullWhenExpireToken(): void
    {
        $handler = $this->getInstance(new JwtConfig('HS256', str_repeat('k', 256), 'iss'));

        $jwt = $handler->generate(new AccessTokenPayload(
            iss: 'iss',
            iat: 0,
            exp: 180,
            nbf: 0,
            jti: 'jti',
        ));

        $this->clock->shouldReceive('now')
            ->with()
            ->andReturn(new DateTimeImmutable())
            ->once();

        $this->assertNull($handler->verify($jwt));
    }

    #[Test]
    public function returnsNullWhenSignatureIsInvalid(): void
    {
        $now = new DateTimeImmutable();

        $handler = $this->getInstance(new JwtConfig('HS256', str_repeat('k', 256), 'iss'));

        $jwt = $handler->generate(new AccessTokenPayload(
            iss: 'iss',
            iat: $now->getTimestamp(),
            exp: $now->modify('+1 hours')->getTimestamp(),
            nbf: $now->getTimestamp(),
            jti: 'jti',
        ));

        $otherKeyHandler = $this->getInstance(new JwtConfig('HS256', str_repeat('x', 256), 'iss'));

        $this->clock->shouldReceive('now')
            ->with()
            ->andReturn($now)
            ->once();

        $this->assertNull($otherKeyHandler->verify($jwt));
    }

    #[Test]
    public function returnsNullWhenJwtIsMalformed(): void
    {
        $handler = $this->getInstance(new JwtConfig('HS256', str_repeat('k', 256), 'iss'));

        $this->clock->shouldReceive('now')
            ->with()
            ->andReturn(new DateTimeImmutable())
            ->once();

        $this->assertNull($handler->verify('not-a-jwt'));
    }

    private function getInstance(JwtConfig $config): JwtHandler
    {
        return new JwtHandler($this->clock, $this->mapper, $config);
    }
}
