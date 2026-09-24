<?php

declare(strict_types=1);

namespace Event\Application\Admin\Assemble;

readonly class AssembledVenue
{
    public function __construct(
        public string $venueId,
        public string $name,
        public string $kindName,
        public int $kindValue,
    ) {
    }
}
