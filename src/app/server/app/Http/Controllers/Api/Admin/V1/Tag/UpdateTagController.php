<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\Tag;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\Tag\UpdatePresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Support\Contracts\MapperInterface;
use Tag\Application\Admin\UseCase\Update\UpdateInputData;
use Tag\Application\Admin\UseCase\Update\UpdateUseCase;

class UpdateTagController extends Controller
{
    public function __construct(
        private readonly MapperInterface $mapper,
        private readonly UpdateUseCase $useCase,
        private readonly UpdatePresenter $presenter,
    ) {
    }

    public function handle(string $tagId, Request $request): JsonResponse
    {
        return $this->buildInput($tagId, $request)
            |> $this->useCase->handle(...)
            |> $this->presenter->present(...);
    }

    private function buildInput(string $tagId, Request $request): UpdateInputData
    {
        return $this->mapper->map(
            UpdateInputData::class,
            [
                ...$request->all(),
                'tagId' => $tagId,
            ],
        );
    }
}
