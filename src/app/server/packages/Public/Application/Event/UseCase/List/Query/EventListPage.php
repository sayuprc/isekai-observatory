<?php

declare(strict_types=1);

namespace Public\Application\Event\UseCase\List\Query;

use SensitiveParameter;

readonly class EventListPage
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
