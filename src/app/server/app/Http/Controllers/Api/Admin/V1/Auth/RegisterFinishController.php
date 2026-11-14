<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\Auth\RegisterFinishPresenter;
use Auth\Application\Admin\UseCase\RegisterFinish\RegisterFinishInputData;
use Auth\Application\Admin\UseCase\RegisterFinish\RegisterFinishUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Support\Contracts\MapperInterface;

final readonly class RegisterFinishController extends Controller
{
    public function __construct(
        private MapperInterface $mapper,
        private RegisterFinishUseCase $useCase,
        private RegisterFinishPresenter $presenter,
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        return $this->mapper->map(RegisterFinishInputData::class, $request->all())
            |> $this->useCase->handle(...)
            |> $this->presenter->present(...);
    }
}
