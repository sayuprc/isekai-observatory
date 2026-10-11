<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\SongTag;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\SongTag\GetPresenter;
use Illuminate\Http\JsonResponse;
use Song\Application\Admin\UseCase\Tag\Get\GetInputData;
use Song\Application\Admin\UseCase\Tag\Get\GetUseCase;

class GetSongTagController extends Controller
{
    public function __construct(
        private readonly GetUseCase $useCase,
        private readonly GetPresenter $presenter,
    ) {
    }

    public function handle(string $songTagId): JsonResponse
    {
        return $this->presenter->present($this->useCase->handle(new GetInputData($songTagId)));
    }
}
