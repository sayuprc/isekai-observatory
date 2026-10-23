<?php

declare(strict_types=1);

namespace Venue\Application\Public\Query;

readonly class VenueListPage
{
    /**
     * @param list<VenueListItem> $venues
     */
    public function __construct(
        public array $venues,
        public ?string $nextPageToken,
    ) {
    }
}
