<?php

declare(strict_types=1);

namespace Viewer\Application\Event\UseCase\List\Query;

readonly class EventSourceSummary
{
    public function __construct(
        public string $displayName,
        public string $url,
        public int $orderNo,
    ) {
    }
}
