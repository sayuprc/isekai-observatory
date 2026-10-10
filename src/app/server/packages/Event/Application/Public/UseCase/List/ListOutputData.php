<?php

declare(strict_types=1);

namespace Event\Application\Public\UseCase\List;

use Event\Application\Public\Query\EventListItem;

readonly class ListOutputData
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
