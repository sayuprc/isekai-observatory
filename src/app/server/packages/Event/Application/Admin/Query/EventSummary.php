<?php

declare(strict_types=1);

namespace Event\Application\Admin\Query;

readonly class EventSummary
{
    public function __construct(
        public string $eventId,
        public string $title,
        public int $typeValue,
        public ?string $startOn,
        public ?string $endOn,
        public int $statusValue,
        public bool $isDisplay,
    ) {
    }
}
