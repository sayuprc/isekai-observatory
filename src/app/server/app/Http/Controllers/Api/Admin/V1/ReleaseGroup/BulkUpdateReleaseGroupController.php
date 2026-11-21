<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\ReleaseGroup;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\ReleaseGroup\BulkUpdatePresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Release\Application\Admin\UseCase\Group\BulkUpdate\BulkUpdateInputData;
use Release\Application\Admin\UseCase\Group\BulkUpdate\BulkUpdateUseCase;
use Support\Contracts\MapperInterface;

final readonly class BulkUpdateReleaseGroupController extends Controller
{
    public function __construct(
        private MapperInterface $mapper,
        private BulkUpdateUseCase $useCase,
        private BulkUpdatePresenter $presenter,
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        return $this->mapper->map(BulkUpdateInputData::class, $request->all())
            |> $this->useCase->handle(...)
            |> $this->presenter->present(...);
    }
}
