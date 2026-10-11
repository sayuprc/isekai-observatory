<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\PersonGroup;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\PersonGroup\GetPresenter;
use Illuminate\Http\JsonResponse;
use Person\Application\Admin\UseCase\Group\Get\GetInputData;
use Person\Application\Admin\UseCase\Group\Get\GetUseCase;

class GetPersonGroupController extends Controller
{
    public function __construct(
        private readonly GetUseCase $useCase,
        private readonly GetPresenter $presenter,
    ) {
    }

    public function handle(string $personGroupId): JsonResponse
    {
        return $this->presenter->present($this->useCase->handle(new GetInputData($personGroupId)));
    }
}
