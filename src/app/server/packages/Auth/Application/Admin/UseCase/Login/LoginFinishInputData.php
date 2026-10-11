<?php

declare(strict_types=1);

namespace Auth\Application\Admin\UseCase\Login;

readonly class LoginFinishInputData
{
    /**
     * @param array<string, mixed> $credential
     */
    public function __construct(
        public string $authCeremonyId,
        public array $credential,
    ) {
    }
}
