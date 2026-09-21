<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\Venue;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\Venue\SearchPresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Support\Contracts\MapperInterface;
use Venue\Application\Admin\UseCase\Search\SearchInputData;
use Venue\Application\Admin\UseCase\Search\SearchUseCase;

class SearchVenueController extends Controller
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
