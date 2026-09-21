<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\Song;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\Song\UpdatePresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Song\Application\Admin\UseCase\Update\UpdateInputData;
use Song\Application\Admin\UseCase\Update\UpdateUseCase;
use Support\Contracts\MapperInterface;

class UpdateSongController extends Controller
{
    public function __construct(
        private readonly MapperInterface $mapper,
        private readonly UpdateUseCase $useCase,
        private readonly UpdatePresenter $presenter,
    ) {
    }

    public function handle(string $songId, Request $request): JsonResponse
    {
        return $this->buildInput($songId, $request)
            |> $this->useCase->handle(...)
            |> $this->presenter->present(...);
    }

    private function buildInput(string $songId, Request $request): UpdateInputData
    {
        return $this->mapper->map(
            UpdateInputData::class,
            [
                ...$request->all(),
                'songId' => $songId,
            ],
        );
    }
}
