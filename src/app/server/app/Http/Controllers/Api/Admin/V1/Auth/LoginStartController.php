<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\Auth\LoginStartPresenter;
use Auth\Application\Admin\UseCase\Login\LoginStartInputData;
use Auth\Application\Admin\UseCase\Login\LoginStartUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Support\Contracts\MapperInterface;

final readonly class LoginStartController extends Controller
{
    public function __construct(
        private MapperInterface $mapper,
        private LoginStartUseCase $useCase,
        private LoginStartPresenter $presenter,
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        return $this->mapper->map(LoginStartInputData::class, $request->all())
            |> $this->useCase->handle(...)
            |> $this->presenter->present(...);
    }
}
