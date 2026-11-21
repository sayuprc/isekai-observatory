<?php

declare(strict_types=1);

namespace Public\Application\Event\UseCase\List;

use Public\Application\Event\UseCase\List\Query\EventListItem;
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
