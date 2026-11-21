<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\Song;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\Song\SearchPresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Song\Application\Admin\UseCase\Search\SearchInputData;
use Song\Application\Admin\UseCase\Search\SearchUseCase;
use Support\Contracts\MapperInterface;

final readonly class SearchSongController extends Controller
{
    public function __construct(
        private MapperInterface $mapper,
        private SearchUseCase $useCase,
        private SearchPresenter $presenter,
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        return $this->mapper->map(SearchInputData::class, $request->query())
            |> $this->useCase->handle(...)
            |> $this->presenter->present(...);
    }
}
