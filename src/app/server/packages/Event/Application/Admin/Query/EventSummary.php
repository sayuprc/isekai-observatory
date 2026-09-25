<?php

declare(strict_types=1);

namespace Event\Application\Admin\Query;

use Event\Domain\Models\EventStatus;
use Event\Domain\Models\EventType;

readonly class EventSummary
{
    public function __construct(
        public string $eventId,
        public string $title,
        public EventType $type,
        public ?string $startOn,
        public ?string $endOn,
        public EventStatus $status,
        public bool $isDisplay,
    ) {
    }
}
