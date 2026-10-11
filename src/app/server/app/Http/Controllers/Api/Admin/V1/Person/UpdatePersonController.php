<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\Person;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\Person\UpdatePresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Person\Application\Admin\UseCase\Update\UpdateInputData;
use Person\Application\Admin\UseCase\Update\UpdateUseCase;
use Support\Contracts\MapperInterface;

class UpdatePersonController extends Controller
{
    public function __construct(
        private readonly MapperInterface $mapper,
        private readonly UpdateUseCase $useCase,
        private readonly UpdatePresenter $presenter,
    ) {
    }

    public function handle(string $personId, Request $request): JsonResponse
    {
        return $this->buildInput($personId, $request)
            |> $this->useCase->handle(...)
            |> $this->presenter->present(...);
    }

    private function buildInput(string $personId, Request $request): UpdateInputData
    {
        return $this->mapper->map(
            UpdateInputData::class,
            [
                ...$request->all(),
                'personId' => $personId,
            ],
        );
    }
}
