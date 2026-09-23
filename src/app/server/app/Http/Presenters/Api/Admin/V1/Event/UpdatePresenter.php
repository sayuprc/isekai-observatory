<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Event;

use Event\Application\Admin\UseCase\Update\UpdateOutputData;
use Illuminate\Http\JsonResponse;

class UpdatePresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    public function present(UpdateOutputData $outputData): JsonResponse
    {
        return response()->json(['event' => $this->converter->toEvent($outputData->event)], 200);
    }
}
