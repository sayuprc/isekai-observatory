<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\Release;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\Release\CreatePresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Release\Application\Admin\UseCase\Create\CreateInputData;
use Release\Application\Admin\UseCase\Create\CreateUseCase;
use Support\Contracts\MapperInterface;

final readonly class CreateReleaseController extends Controller
{
    public function __construct(
        private MapperInterface $mapper,
        private CreateUseCase $useCase,
        private CreatePresenter $presenter,
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        return $this->mapper->map(CreateInputData::class, $request->all())
            |> $this->useCase->handle(...)
            |> $this->presenter->present(...);
    }
}
