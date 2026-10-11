<?php

declare(strict_types=1);

namespace Event\Application\Admin\UseCase\Search;

use Event\Application\Admin\Query\EventSummary;

readonly class SearchOutputData
{
    /** @param list<EventSummary> $events */
    public function __construct(
        public array $events,
        public int $maxPage,
    ) {
    }
}
