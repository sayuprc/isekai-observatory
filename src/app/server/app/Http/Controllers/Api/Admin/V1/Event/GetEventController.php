<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\Event;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\Event\GetPresenter;
use Event\Application\Admin\UseCase\Get\GetInputData;
use Event\Application\Admin\UseCase\Get\GetUseCase;
use Illuminate\Http\JsonResponse;

class GetEventController extends Controller
{
    public function __construct(
        private readonly GetUseCase $useCase,
        private readonly GetPresenter $presenter,
    ) {
    }

    public function handle(string $eventId): JsonResponse
    {
        return $this->presenter->present($this->useCase->handle(new GetInputData($eventId)));
    }
}
