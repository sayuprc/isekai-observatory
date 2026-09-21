<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\ReleaseGroup;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\ReleaseGroup\GetPresenter;
use Illuminate\Http\JsonResponse;
use Release\Application\Admin\UseCase\Group\Get\GetInputData;
use Release\Application\Admin\UseCase\Group\Get\GetUseCase;

class GetReleaseGroupController extends Controller
{
    public function __construct(
        private readonly GetUseCase $useCase,
        private readonly GetPresenter $presenter,
    ) {
    }

    public function handle(string $releaseGroupId): JsonResponse
    {
        return $this->presenter->present($this->useCase->handle(new GetInputData($releaseGroupId)));
    }
}
