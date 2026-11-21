<?php

declare(strict_types=1);

namespace Event\Application\Viewer\List;

use Event\Application\Viewer\List\Query\EventListItem;

readonly class ListOutputData
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
