<?php

declare(strict_types=1);

namespace Event\Application\Public\Query;

readonly class DecodedEventListPageToken
{
    public function __construct(
        public string $title,
        public string $eventId,
    ) {
    }
}
