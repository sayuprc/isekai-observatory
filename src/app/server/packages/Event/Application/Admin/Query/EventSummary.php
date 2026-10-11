<?php

declare(strict_types=1);

namespace Event\Application\Admin\Query;

use Event\Domain\Models\EventStatus;
use Event\Domain\Models\EventType;

readonly class EventSummary
{
    /**
     * @param list<string> $venueNames 開催先名。登録順
     */
    public function __construct(
        public string $eventId,
        public string $title,
        public EventType $type,
        public ?string $startOn,
        public ?string $endOn,
        public EventStatus $status,
        public bool $isDisplay,
        public array $venueNames,
        public int $performanceCount,
        public int $setlistItemCount,
        public int $sourceCount,
    ) {
    }
}
