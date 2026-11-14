<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\PersonGroup;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\PersonGroup\UpdatePresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Person\Application\Admin\UseCase\Group\Update\UpdateInputData;
use Person\Application\Admin\UseCase\Group\Update\UpdateUseCase;
use Support\Contracts\MapperInterface;

final readonly class UpdatePersonGroupController extends Controller
{
    public function __construct(
        private MapperInterface $mapper,
        private UpdateUseCase $useCase,
        private UpdatePresenter $presenter,
    ) {
    }

    public function handle(string $personGroupId, Request $request): JsonResponse
    {
        return $this->mapper->map(UpdateInputData::class, [...$request->all(), 'personGroupId' => $personGroupId])
            |> $this->useCase->handle(...)
            |> $this->presenter->present(...);
    }
}
