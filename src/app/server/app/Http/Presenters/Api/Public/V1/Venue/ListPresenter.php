<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Public\V1\Venue;

use Illuminate\Http\JsonResponse;
use OpenAPI\Public\Client\Model\Venue;
use OpenAPI\Public\Client\Model\VenueKindValue;
use OpenAPI\Public\Client\Model\VenueListResponse;
use Venue\Application\Public\Query\VenueListItem;
use Venue\Application\Public\UseCase\List\ListOutputData;

class ListPresenter
{
    public function present(ListOutputData $outputData): JsonResponse
    {
        return response()->json(
            new VenueListResponse(['next_page_token' => $outputData->nextPageToken])
                ->setItems(array_map($this->toOpenApiVenue(...), $outputData->venues)),
            200,
        );
    }

    private function toOpenApiVenue(VenueListItem $venue): Venue
    {
        return new Venue()
            ->setVenueId($venue->venueId)
            ->setName($venue->name)
            ->setKind(VenueKindValue::from($venue->kind->value));
    }
}
