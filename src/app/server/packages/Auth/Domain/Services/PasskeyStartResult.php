<?php

declare(strict_types=1);

namespace Auth\Domain\Services;

readonly class PasskeyStartResult
{
    /**
     * @param array<string, mixed> $publicKey
     */
    public function __construct(
        public string $optionsJson,
        public array $publicKey,
    ) {
    }
}
