<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\Person;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\Person\GetPresenter;
use Illuminate\Http\JsonResponse;
use Person\Application\Admin\UseCase\Get\GetInputData;
use Person\Application\Admin\UseCase\Get\GetUseCase;

class GetPersonController extends Controller
{
    public function __construct(
        private readonly GetUseCase $useCase,
        private readonly GetPresenter $presenter,
    ) {
    }

    public function handle(string $personId): JsonResponse
    {
        return $this->presenter->present($this->useCase->handle(new GetInputData($personId)));
    }
}
