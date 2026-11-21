<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Venue;

use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\VenueGetResponse;
use Venue\Application\Admin\UseCase\Get\GetOutputData;

class GetPresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    public function present(GetOutputData $outputData): JsonResponse
    {
        return response()->json(
            new VenueGetResponse()->setVenue($this->converter->toOpenApiVenue($outputData->venue)),
            200,
        );
    }
}
