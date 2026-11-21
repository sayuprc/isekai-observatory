<?php

declare(strict_types=1);

namespace Venue\Application\Public\List\Query;

readonly class DecodedVenueListPageToken
{
    public function __construct(
        public string $name,
        public string $venueId,
    ) {
    }
}
