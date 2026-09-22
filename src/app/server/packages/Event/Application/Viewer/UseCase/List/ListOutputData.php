<?php

declare(strict_types=1);

namespace Event\Application\Viewer\UseCase\List;

use Event\Domain\Models\Event;

readonly class ListOutputData
{
    /** @param list<Event> $events */
    public function __construct(
        public array $events,
        public ?string $nextCursor,
    ) {
    }
}
