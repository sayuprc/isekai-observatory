<?php

declare(strict_types=1);

namespace Event\Application\Public\Query;

use Event\Domain\Models\EventStatus;
use Event\Domain\Models\EventType;

readonly class EventListItem
{
    /**
     * @param list<string>          $venueIds
     * @param list<string>          $mediaIds
     * @param list<string>          $releaseIds
     * @param list<EventSourceItem> $sources
     * @param list<PerformanceItem> $performances
     * @param list<SetlistItem>     $setlist
     */
    public function __construct(
        public string $eventId,
        public string $title,
        public string $description,
        public EventType $type,
        public ?string $startOn,
        public ?string $endOn,
        public EventStatus $status,
        public array $venueIds,
        public array $mediaIds,
        public array $releaseIds,
        public array $sources,
        public array $performances,
        public array $setlist,
    ) {
    }
}
