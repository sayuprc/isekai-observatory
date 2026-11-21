<?php

declare(strict_types=1);

namespace Viewer\Application\Event\UseCase\List\Query;

readonly class DecodedEventListCursor
{
    public function __construct(
        public ?string $startOn,
        public string $eventId,
    ) {
    }
}
