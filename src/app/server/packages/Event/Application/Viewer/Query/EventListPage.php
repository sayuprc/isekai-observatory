<?php

declare(strict_types=1);

namespace Event\Application\Viewer\Query;

readonly class EventListPage
{
    /**
     * @param array<EventListItem> $events
     */
    public function __construct(
        public array $events,
        public ?string $nextCursor,
    ) {
    }
}
