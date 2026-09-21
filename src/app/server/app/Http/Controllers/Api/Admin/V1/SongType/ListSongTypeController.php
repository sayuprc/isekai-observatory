<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\SongType;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\SongType\ListPresenter;
use Illuminate\Http\JsonResponse;
use Song\Application\Admin\UseCase\Type\ListUseCase;

class ListSongTypeController extends Controller
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
