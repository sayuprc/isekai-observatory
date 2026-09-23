<?php

declare(strict_types=1);

namespace Event\Application\Viewer\Query;

use Event\Domain\Models\EventStatus;
use Event\Domain\Models\EventType;

readonly class EventListItem
{
    /**
     * @param array<EventVenueSummary>       $venues
     * @param array<EventMediaSummary>       $media        公開 Media のみ
     * @param array<EventSourceSummary>      $sources
     * @param array<EventPerformanceSummary> $performances
     * @param array<EventSetlistItemSummary> $setlist
     */
    public function __construct(
        public string $eventId,
        public string $title,
        public string $description,
        public EventType $type,
        public ?string $startOn,
        public ?string $endOn,
        public EventStatus $status,
        public array $venues,
        public array $media,
        public array $sources,
        public array $performances,
        public array $setlist,
    ) {
    }
}
