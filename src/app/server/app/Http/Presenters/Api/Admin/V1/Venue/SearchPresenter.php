<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Venue;

use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\VenueSearchResponse;
use OpenAPI\Admin\Client\Model\VenueSummary;
use Venue\Application\Admin\UseCase\Search\SearchOutputData;
use Venue\Domain\Models\Venue;

class SearchPresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    public function present(SearchOutputData $outputData): JsonResponse
    {
        return response()->json(
            new VenueSearchResponse()
                ->setVenues(array_map(
                    fn (Venue $venue): VenueSummary => $this->converter->toOpenApiVenueSummary(
                        $venue,
                        $outputData->usageCounts[$venue->venueId->value] ?? 0,
                    ),
                    $outputData->venues,
                ))
                ->setMaxPage($outputData->maxPage),
            200,
        );
    }
}
