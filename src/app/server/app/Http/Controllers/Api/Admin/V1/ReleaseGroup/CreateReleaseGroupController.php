<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\ReleaseGroup;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\ReleaseGroup\CreatePresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Release\Application\Admin\UseCase\Group\Create\CreateInputData;
use Release\Application\Admin\UseCase\Group\Create\CreateUseCase;
use Support\Contracts\MapperInterface;

class CreateReleaseGroupController extends Controller
{
    public function __construct(
        private readonly MapperInterface $mapper,
        private readonly CreateUseCase $useCase,
        private readonly CreatePresenter $presenter,
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        return $this->mapper->map(CreateInputData::class, $request->all())
            |> $this->useCase->handle(...)
            |> $this->presenter->present(...);
    }
}
