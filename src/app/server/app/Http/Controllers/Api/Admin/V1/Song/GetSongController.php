<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\Song;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\Song\GetPresenter;
use Illuminate\Http\JsonResponse;
use Song\Application\Admin\UseCase\Get\GetInputData;
use Song\Application\Admin\UseCase\Get\GetUseCase;

final readonly class GetSongController extends Controller
{
    public function __construct(
        private GetUseCase $useCase,
        private GetPresenter $presenter,
    ) {
    }

    public function handle(string $songId): JsonResponse
    {
        return new GetInputData($songId)
            |> $this->useCase->handle(...)
            |> $this->presenter->present(...);
    }
}
