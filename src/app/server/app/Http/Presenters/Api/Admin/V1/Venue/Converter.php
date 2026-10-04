<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Venue;

use OpenAPI\Admin\Client\Model\Venue as OpenApiVenue;
use OpenAPI\Admin\Client\Model\VenueKind as OpenApiVenueKind;
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
            ->setKind($this->toOpenApiKind($venue));
    }

    public function toOpenApiVenueSummary(Venue $venue, int $eventCount): OpenApiVenueSummary
    {
        return new OpenApiVenueSummary()
            ->setVenueId($venue->venueId->value)
            ->setName($venue->name->value)
            ->setKind($this->toOpenApiKind($venue))
            ->setEventCount($eventCount);
    }

    private function toOpenApiKind(Venue $venue): OpenApiVenueKind
    {
        return new OpenApiVenueKind()
            ->setName($venue->kind->getName())
            ->setValue(VenueKindValue::from($venue->kind->value));
    }
}
