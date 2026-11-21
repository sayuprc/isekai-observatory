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

final readonly class UpdateEventController extends Controller
{
    public function __construct(
        private MapperInterface $mapper,
        private UpdateUseCase $useCase,
        private UpdatePresenter $presenter,
    ) {
    }

    public function handle(string $eventId, Request $request): JsonResponse
    {
        return $this->mapper->map(UpdateInputData::class, [...$request->all(), 'eventId' => $eventId])
            |> $this->useCase->handle(...)
            |> $this->presenter->present(...);
    }
}
