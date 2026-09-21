<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Venue;

use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\VenueSearchResponse;
use Venue\Application\Admin\UseCase\Search\SearchOutputData;

class SearchPresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    public function present(SearchOutputData $outputData): JsonResponse
    {
        return response()->json(
            new VenueSearchResponse()
                ->setVenues(array_map($this->converter->toOpenApiVenue(...), $outputData->venues))
                ->setMaxPage($outputData->maxPage),
            200,
        );
    }
}
