<?php

declare(strict_types=1);

namespace Event\Application\Public\Query;

readonly class EventListPage
{
    /**
     * @param list<EventListItem> $events
     */
    public function __construct(
        public array $events,
        public ?string $nextPageToken,
    ) {
    }
}
