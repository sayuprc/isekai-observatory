<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\ReleaseGroup;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\ReleaseGroup\GetPresenter;
use Illuminate\Http\JsonResponse;
use Release\Application\Admin\UseCase\Group\Get\GetInputData;
use Release\Application\Admin\UseCase\Group\Get\GetUseCase;

final readonly class GetReleaseGroupController extends Controller
{
    public function __construct(
        private GetUseCase $useCase,
        private GetPresenter $presenter,
    ) {
    }

    public function handle(string $releaseGroupId): JsonResponse
    {
        return new GetInputData($releaseGroupId)
            |> $this->useCase->handle(...)
            |> $this->presenter->present(...);
    }
}
