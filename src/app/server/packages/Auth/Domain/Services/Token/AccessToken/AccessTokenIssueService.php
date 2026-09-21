<?php

declare(strict_types=1);

namespace Auth\Domain\Services\Token\AccessToken;

use Auth\Domain\Models\Token\AccessToken\AccessToken;
use Auth\Domain\Models\Token\AccessToken\AccessTokenFactoryInterface;
use Support\Contracts\ClockInterface;

class AccessTokenIssueService
{
    private const int TTL_HOUR = 1;

    public function __construct(
        private readonly ClockInterface $clock,
        private readonly JwtConfig $config,
        private readonly AccessTokenFactoryInterface $factory,
    ) {
    }

    public function issue(string $id): AccessToken
    {
        $now = $this->clock->now();

        $payload = new AccessTokenPayload(
            iss: $this->config->issuer,
            iat: $now->getTimestamp(),
            exp: $now->modify('+' . self::TTL_HOUR . ' hours')->getTimestamp(),
            nbf: $now->getTimestamp(),
            jti: $id,
        );

        return $this->factory->create($payload);
    }
}
