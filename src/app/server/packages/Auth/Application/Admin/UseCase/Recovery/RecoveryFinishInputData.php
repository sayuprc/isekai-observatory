<?php

declare(strict_types=1);

namespace Auth\Application\Admin\UseCase\Recovery;

readonly class RecoveryFinishInputData
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
