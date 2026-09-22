<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\Event;

use App\Http\Presenters\Api\Admin\V1\Event\Converter;
use Event\Application\Admin\UseCase\Create\CreateInputData;
use Event\Application\Admin\UseCase\Create\CreateUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CreateEventController
{
    public function __construct(
        private readonly CreateUseCase $useCase,
        private readonly Converter $converter,
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        /** @var array<string, mixed> $data */
        $data = $request->all();

        return response()->json(['event' => $this->converter->toEvent($this->useCase->handle(new CreateInputData($data))->event)]);
    }
}
