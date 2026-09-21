<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Venue;

use OpenAPI\Admin\Client\Model\Venue as OpenApiVenue;
use OpenAPI\Admin\Client\Model\VenueKind as OpenApiVenueKind;
use OpenAPI\Admin\Client\Model\VenueKindValue;
use Venue\Domain\Models\Venue;

class Converter
{
    public function toOpenApiVenue(Venue $venue): OpenApiVenue
    {
        return new OpenApiVenue()
            ->setVenueId($venue->venueId->value)
            ->setName($venue->name->value)
            ->setKind(
                new OpenApiVenueKind()
                    ->setName($venue->kind->getName())
                    ->setValue(VenueKindValue::from($venue->kind->value)),
            );
    }
}
