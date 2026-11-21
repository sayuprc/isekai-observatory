<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Event;

use Event\Application\Admin\UseCase\Update\UpdateOutputData;
use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\EventUpdateResponse;

class UpdatePresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    public function present(UpdateOutputData $outputData): JsonResponse
    {
        return response()->json(
            new EventUpdateResponse()->setEvent($this->converter->toOpenApiEvent($outputData->event)),
            200,
        );
    }
}
