<?php

declare(strict_types=1);

namespace Venue\Application\Public\UseCase\List;

use Venue\Application\Public\Query\VenueListItem;

readonly class ListOutputData
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
