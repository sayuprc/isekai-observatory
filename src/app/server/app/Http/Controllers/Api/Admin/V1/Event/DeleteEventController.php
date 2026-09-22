<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\Event;

use Event\Application\Admin\UseCase\Delete\DeleteInputData;
use Event\Application\Admin\UseCase\Delete\DeleteUseCase;
use Illuminate\Http\JsonResponse;

class DeleteEventController
{
    public function __construct(private readonly DeleteUseCase $useCase)
    {
    }

    public function handle(string $eventId): JsonResponse
    {
        $this->useCase->handle(new DeleteInputData($eventId));

        return response()->json(status: 204);
    }
}
