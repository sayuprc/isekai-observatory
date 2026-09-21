<?php

declare(strict_types=1);

namespace Auth\Domain\Services;

readonly class PasskeyAuthenticationResult
{
    public function __construct(
        public string $credentialId,
        public int $signCount,
    ) {
    }
}
