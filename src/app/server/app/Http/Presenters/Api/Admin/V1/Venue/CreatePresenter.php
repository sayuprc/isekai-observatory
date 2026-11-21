<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Venue;

use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\VenueCreateResponse;
use Venue\Application\Admin\UseCase\Create\CreateOutputData;

class CreatePresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    public function present(CreateOutputData $outputData): JsonResponse
    {
        return response()->json(
            new VenueCreateResponse()->setVenue($this->converter->toOpenApiVenue($outputData->venue)),
            200,
        );
    }
}
