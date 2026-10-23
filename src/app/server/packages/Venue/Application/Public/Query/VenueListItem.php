<?php

declare(strict_types=1);

namespace Venue\Application\Public\Query;

use Venue\Domain\Models\VenueKind;

readonly class VenueListItem
{
    public function __construct(
        public string $venueId,
        public string $name,
        public VenueKind $kind,
    ) {
    }
}
