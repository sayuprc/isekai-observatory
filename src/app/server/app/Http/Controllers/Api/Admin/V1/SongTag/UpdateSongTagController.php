<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\SongTag;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\SongTag\UpdatePresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Song\Application\Admin\UseCase\Tag\Update\UpdateInputData;
use Song\Application\Admin\UseCase\Tag\Update\UpdateUseCase;
use Support\Contracts\MapperInterface;

class UpdateSongTagController extends Controller
{
    public function __construct(
        private readonly MapperInterface $mapper,
        private readonly UpdateUseCase $useCase,
        private readonly UpdatePresenter $presenter,
    ) {
    }

    public function handle(string $songTagId, Request $request): JsonResponse
    {
        return $this->buildInput($songTagId, $request)
            |> $this->useCase->handle(...)
            |> $this->presenter->present(...);
    }

    private function buildInput(string $songTagId, Request $request): UpdateInputData
    {
        return $this->mapper->map(
            UpdateInputData::class,
            [
                ...$request->all(),
                'songTagId' => $songTagId,
            ],
        );
    }
}
