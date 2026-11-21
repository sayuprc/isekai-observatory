<?php

declare(strict_types=1);

namespace Public\Application\Event\UseCase\List\Query;

readonly class DecodedEventListPageToken
{
    public function __construct(
        public string $title,
        public string $eventId,
    ) {
    }
}
