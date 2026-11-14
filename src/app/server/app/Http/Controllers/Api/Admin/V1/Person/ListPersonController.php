<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\Person;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\Person\ListPresenter;
use Illuminate\Http\JsonResponse;
use Person\Application\Admin\UseCase\List\ListUseCase;

final readonly class ListPersonController extends Controller
{
    public function __construct(
        private ListUseCase $useCase,
        private ListPresenter $presenter,
    ) {
    }

    public function handle(): JsonResponse
    {
        return $this->useCase->handle()
            |> $this->presenter->present(...);
    }
}
