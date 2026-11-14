<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\Media;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\Media\BulkUpdatePresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Media\Application\Admin\UseCase\BulkUpdate\BulkUpdateInputData;
use Media\Application\Admin\UseCase\BulkUpdate\BulkUpdateUseCase;
use Support\Contracts\MapperInterface;

final readonly class BulkUpdateMediaController extends Controller
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
