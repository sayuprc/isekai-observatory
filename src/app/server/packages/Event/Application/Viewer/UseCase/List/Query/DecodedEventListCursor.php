<?php

declare(strict_types=1);

namespace Event\Application\Viewer\UseCase\List\Query;

readonly class DecodedEventListCursor
{
    public function __construct(
        public ?string $startOn,
        public string $eventId,
    ) {
    }
}
