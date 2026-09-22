<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\Event;

use App\Http\Presenters\Api\Admin\V1\Event\Converter;
use Event\Application\Admin\UseCase\Update\UpdateInputData;
use Event\Application\Admin\UseCase\Update\UpdateUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UpdateEventController
{
    public function __construct(
        private readonly UpdateUseCase $useCase,
        private readonly Converter $converter,
    ) {
    }

    public function handle(string $eventId, Request $request): JsonResponse
    {
        /** @var array<string, mixed> $data */
        $data = $request->all();

        return response()->json(['event' => $this->converter->toEvent($this->useCase->handle(new UpdateInputData($eventId, $data))->event)]);
    }
}
