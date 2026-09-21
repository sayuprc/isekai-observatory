<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\Auth\RefreshPresenter;
use Auth\Application\Admin\UseCase\Refresh\RefreshInputData;
use Auth\Application\Admin\UseCase\Refresh\RefreshUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Support\Contracts\MapperInterface;

class RefreshController extends Controller
{
    public function __construct(
        private readonly MapperInterface $mapper,
        private readonly RefreshUseCase $useCase,
        private readonly RefreshPresenter $presenter,
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        return $this->mapper->map(RefreshInputData::class, $request->all())
            |> $this->useCase->handle(...)
            |> $this->presenter->present(...);
    }
}
