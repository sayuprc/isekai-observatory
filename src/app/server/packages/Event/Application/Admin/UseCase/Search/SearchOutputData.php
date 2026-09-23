<?php

declare(strict_types=1);

namespace Event\Application\Admin\UseCase\Search;

use Event\Domain\Models\Event;

readonly class SearchOutputData
{
    /** @param list<Event> $events */
    public function __construct(
        public array $events,
        public int $maxPage,
    ) {
    }
}
