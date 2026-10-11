<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Event;

use Event\Application\Admin\UseCase\Create\CreateOutputData;
use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\EventCreateResponse;

class CreatePresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    public function present(CreateOutputData $outputData): JsonResponse
    {
        return response()->json(
            new EventCreateResponse()->setEvent($this->converter->toOpenApiEvent($outputData->event)),
            200,
        );
    }
}
