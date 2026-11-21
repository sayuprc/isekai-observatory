<?php

declare(strict_types=1);

namespace Viewer\Application\Event\UseCase\List\Query;

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
