<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Event;

use Event\Application\Admin\UseCase\Get\GetOutputData;
use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\EventGetResponse;

class GetPresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    public function present(GetOutputData $outputData): JsonResponse
    {
        return response()->json(
            new EventGetResponse()->setEvent($this->converter->toOpenApiEvent($outputData->event)),
            200,
        );
    }
}
