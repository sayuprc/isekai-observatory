<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Public\V1\Tag;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Public\V1\Tag\ListPresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tag\Application\Public\UseCase\List\ListInputData;
use Tag\Application\Public\UseCase\List\ListUseCase;

class ListTagController extends Controller
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
        $pageToken = $request->query('pageToken');
        $pageSize = $request->integer('pageSize');

        return new ListInputData(
            is_string($pageToken) ? $pageToken : null,
            $pageSize > 0 ? $pageSize : null,
        );
    }
}
