<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\Event;

use App\Http\Presenters\Api\Admin\V1\Event\Converter;
use Event\Application\Admin\UseCase\Get\GetInputData;
use Event\Application\Admin\UseCase\Get\GetUseCase;
use Illuminate\Http\JsonResponse;

class GetEventController
{
    public function __construct(
        private readonly GetUseCase $useCase,
        private readonly Converter $converter,
    ) {
    }

    public function handle(string $eventId): JsonResponse
    {
        return response()->json(['event' => $this->converter->toEvent($this->useCase->handle(new GetInputData($eventId))->event)]);
    }
}
