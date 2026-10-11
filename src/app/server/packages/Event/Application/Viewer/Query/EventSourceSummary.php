<?php

declare(strict_types=1);

namespace Event\Application\Viewer\Query;

readonly class EventSourceSummary
{
    public function __construct(
        public string $displayName,
        public string $url,
        public int $orderNo,
    ) {
    }
}
