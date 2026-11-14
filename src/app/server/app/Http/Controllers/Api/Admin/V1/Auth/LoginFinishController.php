<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\Auth\LoginFinishPresenter;
use Auth\Application\Admin\UseCase\Login\LoginFinishInputData;
use Auth\Application\Admin\UseCase\Login\LoginFinishUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Support\Contracts\MapperInterface;

final readonly class LoginFinishController extends Controller
{
    public function __construct(
        private MapperInterface $mapper,
        private LoginFinishUseCase $useCase,
        private LoginFinishPresenter $presenter,
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        return $this->mapper->map(LoginFinishInputData::class, $request->all())
            |> $this->useCase->handle(...)
            |> $this->presenter->present(...);
    }
}
