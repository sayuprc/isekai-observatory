<?php

declare(strict_types=1);

namespace Event\Application\Viewer\Query;

readonly class EventSetlistItemSummary
{
    /**
     * @param array<EventPerformanceSummary> $performances
     */
    public function __construct(
        public int $orderNo,
        public ?string $label,
        public array $performances,
    ) {
    }
}
