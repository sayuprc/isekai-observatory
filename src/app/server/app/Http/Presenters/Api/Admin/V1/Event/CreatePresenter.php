<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Event;

use Event\Application\Admin\UseCase\Create\CreateOutputData;
use Illuminate\Http\JsonResponse;

class CreatePresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    public function present(CreateOutputData $outputData): JsonResponse
    {
        return response()->json(['event' => $this->converter->toEvent($outputData->event)], 200);
    }
}
