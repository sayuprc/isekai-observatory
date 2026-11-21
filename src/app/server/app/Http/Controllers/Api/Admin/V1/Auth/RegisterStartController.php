<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\Auth\RegisterStartPresenter;
use Auth\Application\Admin\UseCase\RegisterStart\RegisterStartInputData;
use Auth\Application\Admin\UseCase\RegisterStart\RegisterStartUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Support\Contracts\MapperInterface;

final readonly class RegisterStartController extends Controller
{
    public function __construct(
        private MapperInterface $mapper,
        private RegisterStartUseCase $useCase,
        private RegisterStartPresenter $presenter,
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        return $this->mapper->map(RegisterStartInputData::class, $request->all())
            |> $this->useCase->handle(...)
            |> $this->presenter->present(...);
    }
}
