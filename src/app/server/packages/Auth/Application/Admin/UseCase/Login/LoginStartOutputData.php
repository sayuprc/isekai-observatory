<?php

declare(strict_types=1);

namespace Auth\Application\Admin\UseCase\Login;

readonly class LoginStartOutputData
{
    /**
     * @param array<string, mixed> $publicKey
     */
    public function __construct(
        public string $authCeremonyId,
        public array $publicKey,
    ) {
    }
}
