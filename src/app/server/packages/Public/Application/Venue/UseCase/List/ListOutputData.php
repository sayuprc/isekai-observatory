<?php

declare(strict_types=1);

namespace Public\Application\Venue\UseCase\List;

use Public\Application\Venue\UseCase\List\Query\VenueListItem;
use SensitiveParameter;

readonly class ListOutputData
{
    /**
     * @param list<VenueListItem> $venues
     */
    public function __construct(
        public array $venues,
        #[SensitiveParameter]
        public ?string $nextPageToken,
    ) {
    }
}
