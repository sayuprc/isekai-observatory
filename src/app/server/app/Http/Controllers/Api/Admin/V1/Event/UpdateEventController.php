<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\Event;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\Event\UpdatePresenter;
use Event\Application\Admin\UseCase\Update\UpdateInputData;
use Event\Application\Admin\UseCase\Update\UpdateUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Support\Contracts\MapperInterface;

class UpdateEventController extends Controller
{
    public function __construct(
        private readonly MapperInterface $mapper,
        private readonly UpdateUseCase $useCase,
        private readonly UpdatePresenter $presenter,
    ) {
    }

    public function handle(string $eventId, Request $request): JsonResponse
    {
        return $this->mapper->map(UpdateInputData::class, [...$request->all(), 'eventId' => $eventId])
            |> $this->useCase->handle(...)
            |> $this->presenter->present(...);
    }
}
