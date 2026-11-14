<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\Release;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\Release\GetPresenter;
use Illuminate\Http\JsonResponse;
use Release\Application\Admin\UseCase\Get\GetInputData;
use Release\Application\Admin\UseCase\Get\GetUseCase;

final readonly class GetReleaseController extends Controller
{
    public function __construct(
        private GetUseCase $useCase,
        private GetPresenter $presenter,
    ) {
    }

    public function handle(string $releaseId): JsonResponse
    {
        return new GetInputData($releaseId)
            |> $this->useCase->handle(...)
            |> $this->presenter->present(...);
    }
}
