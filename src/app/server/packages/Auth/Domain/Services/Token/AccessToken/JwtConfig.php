<?php

declare(strict_types=1);

namespace Auth\Domain\Services\Token\AccessToken;

class JwtConfig
{
    public function __construct(
        public readonly string $alg,
        public readonly string $key,
        public readonly string $issuer,
    ) {
    }
}
