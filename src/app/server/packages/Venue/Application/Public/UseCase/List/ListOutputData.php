<?php

declare(strict_types=1);

namespace Venue\Application\Public\UseCase\List;

use SensitiveParameter;
use Venue\Application\Public\UseCase\List\Query\VenueListItem;

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
