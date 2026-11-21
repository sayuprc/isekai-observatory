<?php

declare(strict_types=1);

namespace Public\Application\Venue\UseCase\List\Query;

use SensitiveParameter;

readonly class VenueListPage
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
