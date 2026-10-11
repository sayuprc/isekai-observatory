<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\AuditLog;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\AuditLog\SearchPresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Support\Contracts\MapperInterface;
use Support\UseCase\AuditLog\Search\SearchInputData;
use Support\UseCase\AuditLog\Search\SearchUseCase;

class SearchAuditLogController extends Controller
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
