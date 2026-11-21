<?php

declare(strict_types=1);

namespace Venue\Application\Admin\UseCase\Update;

readonly class UpdateInputData
{
    public function __construct(
        public string $venueId,
        public string $name,
        public int $kind,
    ) {
    }
}
