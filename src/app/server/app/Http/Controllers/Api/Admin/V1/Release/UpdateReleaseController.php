<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\Release;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\Release\UpdatePresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Release\Application\Admin\UseCase\Update\UpdateInputData;
use Release\Application\Admin\UseCase\Update\UpdateUseCase;
use Support\Contracts\MapperInterface;

class UpdateReleaseController extends Controller
{
    public function __construct(
        private readonly MapperInterface $mapper,
        private readonly UpdateUseCase $useCase,
        private readonly UpdatePresenter $presenter,
    ) {
    }

    public function handle(string $releaseId, Request $request): JsonResponse
    {
        return $this->mapper->map(
            UpdateInputData::class,
            [
                ...$request->all(),
                'releaseId' => $releaseId,
            ],
        )
            |> $this->useCase->handle(...)
            |> $this->presenter->present(...);
    }
}
