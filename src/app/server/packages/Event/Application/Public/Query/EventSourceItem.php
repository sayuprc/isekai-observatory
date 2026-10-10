<?php

declare(strict_types=1);

namespace Event\Application\Public\Query;

readonly class EventSourceItem
{
    public function __construct(
        public string $displayName,
        public string $url,
    ) {
    }
}
