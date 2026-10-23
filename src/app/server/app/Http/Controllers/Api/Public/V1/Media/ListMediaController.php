<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Public\V1\Media;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Public\V1\Media\ListPresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Media\Application\Public\UseCase\List\ListInputData;
use Media\Application\Public\UseCase\List\ListUseCase;

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
        $pageToken = $request->query('pageToken');
        $pageSize = $request->integer('pageSize');

        return new ListInputData(
            is_string($pageToken) ? $pageToken : null,
            $pageSize > 0 ? $pageSize : null,
        );
    }
}
