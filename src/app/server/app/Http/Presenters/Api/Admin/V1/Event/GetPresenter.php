<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Event;

use Event\Application\Admin\UseCase\Get\GetOutputData;
use Illuminate\Http\JsonResponse;

class GetPresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    public function present(GetOutputData $outputData): JsonResponse
    {
        return response()->json(['event' => $this->converter->toEvent($outputData->event)], 200);
    }
}
