<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\ReleaseGroup;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\ReleaseGroup\UpdatePresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Release\Application\Admin\UseCase\Group\Update\UpdateInputData;
use Release\Application\Admin\UseCase\Group\Update\UpdateUseCase;
use Support\Contracts\MapperInterface;

class UpdateReleaseGroupController extends Controller
{
    public function __construct(
        private readonly MapperInterface $mapper,
        private readonly UpdateUseCase $useCase,
        private readonly UpdatePresenter $presenter,
    ) {
    }

    public function handle(string $releaseGroupId, Request $request): JsonResponse
    {
        return $this->mapper->map(
            UpdateInputData::class,
            [
                ...$request->all(),
                'releaseGroupId' => $releaseGroupId,
            ],
        )
            |> $this->useCase->handle(...)
            |> $this->presenter->present(...);
    }
}
