<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\PersonGroup;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\PersonGroup\CreatePresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Person\Application\Admin\UseCase\Group\Create\CreateInputData;
use Person\Application\Admin\UseCase\Group\Create\CreateUseCase;
use Support\Contracts\MapperInterface;

class CreatePersonGroupController extends Controller
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
