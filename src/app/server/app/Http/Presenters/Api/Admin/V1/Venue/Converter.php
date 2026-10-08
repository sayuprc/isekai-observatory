<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Venue;

use OpenAPI\Admin\Client\Model\Venue as OpenApiVenue;
use OpenAPI\Admin\Client\Model\VenueKindValue;
use OpenAPI\Admin\Client\Model\VenueSummary as OpenApiVenueSummary;
use Venue\Domain\Models\Venue;

class Converter
{
    public function toOpenApiVenue(Venue $venue): OpenApiVenue
    {
        return new OpenApiVenue()
            ->setVenueId($venue->venueId->value)
            ->setName($venue->name->value)
            ->setKind(VenueKindValue::from($venue->kind->value));
    }

    public function toOpenApiVenueSummary(Venue $venue, int $eventCount): OpenApiVenueSummary
    {
        return new OpenApiVenueSummary()
            ->setVenueId($venue->venueId->value)
            ->setName($venue->name->value)
            ->setKind(VenueKindValue::from($venue->kind->value))
            ->setEventCount($eventCount);
    }
}
