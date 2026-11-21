<?php

declare(strict_types=1);

namespace Viewer\Application\Event\UseCase\List\Query;

use Venue\Domain\Models\VenueKind;

readonly class EventVenueSummary
{
    public function __construct(
        public string $venueId,
        public string $name,
        public VenueKind $kind,
    ) {
    }
}
