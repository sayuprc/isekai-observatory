<?php

declare(strict_types=1);

namespace Event\Application\Public\UseCase\List;

use Event\Application\Public\Query\EventListItem;
use SensitiveParameter;

readonly class ListOutputData
{
    /**
     * @param list<EventListItem> $events
     */
    public function __construct(
        public array $events,
        #[SensitiveParameter]
        public ?string $nextPageToken,
    ) {
    }
}
