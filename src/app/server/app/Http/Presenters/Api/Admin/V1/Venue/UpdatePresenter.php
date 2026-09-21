<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Venue;

use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\VenueUpdateResponse;
use Venue\Application\Admin\UseCase\Update\UpdateOutputData;

class UpdatePresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    public function present(UpdateOutputData $outputData): JsonResponse
    {
        return response()->json(
            new VenueUpdateResponse()->setVenue($this->converter->toOpenApiVenue($outputData->venue)),
            200,
        );
    }
}
