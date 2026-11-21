<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\Person;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\Person\GetPresenter;
use Illuminate\Http\JsonResponse;
use Person\Application\Admin\Get\GetInputData;
use Person\Application\Admin\Get\GetUseCase;

final readonly class GetPersonController extends Controller
{
    public function __construct(
        private GetUseCase $useCase,
        private GetPresenter $presenter,
    ) {
    }

    public function handle(string $personId): JsonResponse
    {
        return $this->presenter->present($this->useCase->handle(new GetInputData($personId)));
    }
}
