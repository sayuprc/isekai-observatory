<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\SongTag;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\SongTag\ListPresenter;
use Illuminate\Http\JsonResponse;
use Song\Application\Admin\UseCase\Tag\List\ListUseCase;

class ListSongTagController extends Controller
{
    public function __construct(
        private readonly ListUseCase $useCase,
        private readonly ListPresenter $presenter,
    ) {
    }

    public function handle(): JsonResponse
    {
        return $this->presenter->present($this->useCase->handle());
    }
}
