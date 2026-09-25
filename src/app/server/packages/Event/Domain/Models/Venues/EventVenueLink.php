<?php

declare(strict_types=1);

namespace Event\Domain\Models\Venues;

use Support\Domain\ValueObjects\OrderNo;
use Venue\Domain\Models\VenueId;

readonly class EventVenueLink
{
    public function __construct(
        public VenueId $venueId,
        public OrderNo $orderNo,
    ) {
    }

    /**
     * @return array{venue_id: string, order_no: int}
     */
    public function toArray(): array
    {
        return [
            'venue_id' => $this->venueId->value,
            'order_no' => $this->orderNo->value,
        ];
    }
}
