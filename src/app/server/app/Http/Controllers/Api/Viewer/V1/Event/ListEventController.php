<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Viewer\V1\Event;

use App\Http\Presenters\Api\Viewer\V1\Event\ListPresenter;
use Event\Application\Viewer\UseCase\List\ListInputData;
use Event\Application\Viewer\UseCase\List\ListUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListEventController
{
    public function __construct(
        private readonly ListUseCase $useCase,
        private readonly ListPresenter $presenter,
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        return $this->presenter->present($this->useCase->handle(new ListInputData(is_string($request->query('cursor')) ? $request->query('cursor') : null, $request->integer('limit') > 0 ? $request->integer('limit') : null)));
    }
}
