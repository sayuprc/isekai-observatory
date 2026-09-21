<?php

declare(strict_types=1);

namespace Auth\Infrastructures\Token\AccessToken;

use Auth\Domain\Services\Token\AccessToken\AccessTokenPayload;
use Auth\Domain\Services\Token\AccessToken\JwtConfig;
use Auth\Domain\Services\Token\AccessToken\JwtHandlerInterface;
use DomainException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use InvalidArgumentException;
use Override;
use Support\Contracts\ClockInterface;
use Support\Contracts\MapperInterface;
use UnexpectedValueException;

readonly class JwtHandler implements JwtHandlerInterface
{
    public function __construct(
        private ClockInterface $clock,
        private MapperInterface $mapper,
        private JwtConfig $config,
    ) {
    }

    #[Override]
    public function generate(AccessTokenPayload $payload): string
    {
        return JWT::encode($payload->toArray(), $this->config->key, $this->config->alg);
    }

    #[Override]
    public function verify(string $jwt): ?AccessTokenPayload
    {
        JWT::$timestamp = $this->clock->now()->getTimestamp();

        try {
            $decoded = JWT::decode($jwt, new Key($this->config->key, $this->config->alg));
        } catch (DomainException|InvalidArgumentException|UnexpectedValueException) {
            // 失効 (ExpiredException) のほか、署名不正・形式不正などの検証失敗をすべて含む
            return null;
        }

        $payload = $this->mapper->map(AccessTokenPayload::class, $decoded);

        if ($payload->iss !== $this->config->issuer) {
            return null;
        }

        return $payload;
    }
}
