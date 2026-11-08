<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\Event;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\Event\BulkUpdatePresenter;
use Event\Application\Admin\UseCase\BulkUpdate\BulkUpdateInputData;
use Event\Application\Admin\UseCase\BulkUpdate\BulkUpdateUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Support\Contracts\MapperInterface;

class BulkUpdateEventController extends Controller
{
    public function __construct(
        private readonly MapperInterface $mapper,
        private readonly BulkUpdateUseCase $useCase,
        private readonly BulkUpdatePresenter $presenter,
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        return $this->mapper->map(BulkUpdateInputData::class, $request->all())
            |> $this->useCase->handle(...)
            |> $this->presenter->present(...);
    }
}
