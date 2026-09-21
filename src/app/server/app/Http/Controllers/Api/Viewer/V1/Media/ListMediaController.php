<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Viewer\V1\Media;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Viewer\V1\Media\ListPresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Media\Application\Viewer\UseCase\List\ListInputData;
use Media\Application\Viewer\UseCase\List\ListUseCase;

class ListMediaController extends Controller
{
    public function __construct(
        private readonly ListUseCase $useCase,
        private readonly ListPresenter $presenter,
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        return $this->buildInput($request)
            |> $this->useCase->handle(...)
            |> $this->presenter->present(...);
    }

    private function buildInput(Request $request): ListInputData
    {
        $cursor = $request->query('cursor');
        $limit = $request->integer('limit');

        return new ListInputData(
            is_string($cursor) ? $cursor : null,
            $limit > 0 ? $limit : null,
        );
    }
}
