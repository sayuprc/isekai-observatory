<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\Tag;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\Tag\GetPresenter;
use Illuminate\Http\JsonResponse;
use Tag\Application\Admin\UseCase\Get\GetInputData;
use Tag\Application\Admin\UseCase\Get\GetUseCase;

class GetTagController extends Controller
{
    public function __construct(
        private readonly GetUseCase $useCase,
        private readonly GetPresenter $presenter,
    ) {
    }

    public function handle(string $tagId): JsonResponse
    {
        return $this->presenter->present($this->useCase->handle(new GetInputData($tagId)));
    }
}
