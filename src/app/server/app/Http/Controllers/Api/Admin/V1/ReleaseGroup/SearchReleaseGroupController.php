<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\ReleaseGroup;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\ReleaseGroup\SearchPresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Release\Application\Admin\UseCase\Group\Search\SearchInputData;
use Release\Application\Admin\UseCase\Group\Search\SearchUseCase;
use Support\Contracts\MapperInterface;

class SearchReleaseGroupController extends Controller
{
    public function __construct(
        private readonly MapperInterface $mapper,
        private readonly SearchUseCase $useCase,
        private readonly SearchPresenter $presenter,
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        return $this->mapper->map(SearchInputData::class, $request->query())
            |> $this->useCase->handle(...)
            |> $this->presenter->present(...);
    }
}
