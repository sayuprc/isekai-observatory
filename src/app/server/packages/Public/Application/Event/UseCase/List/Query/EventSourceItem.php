<?php

declare(strict_types=1);

namespace Public\Application\Event\UseCase\List\Query;

readonly class EventSourceItem
{
    public function __construct(
        public string $displayName,
        public string $url,
    ) {
    }
}
