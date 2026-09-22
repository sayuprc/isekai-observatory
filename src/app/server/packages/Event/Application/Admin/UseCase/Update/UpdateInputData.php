<?php

declare(strict_types=1);

namespace Event\Application\Admin\UseCase\Update;

readonly class UpdateInputData
{
    /** @param array<string, mixed> $data */
    public function __construct(
        public string $eventId,
        public array $data,
    ) {
    }
}
