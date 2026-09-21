<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\Release;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\Release\GetPresenter;
use Illuminate\Http\JsonResponse;
use Release\Application\Admin\UseCase\Get\GetInputData;
use Release\Application\Admin\UseCase\Get\GetUseCase;

class GetReleaseController extends Controller
{
    public function __construct(
        private readonly GetUseCase $useCase,
        private readonly GetPresenter $presenter,
    ) {
    }

    public function handle(string $releaseId): JsonResponse
    {
        return $this->presenter->present($this->useCase->handle(new GetInputData($releaseId)));
    }
}
