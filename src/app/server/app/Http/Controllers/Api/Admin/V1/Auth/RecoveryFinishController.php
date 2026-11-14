<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\Auth\RecoveryFinishPresenter;
use Auth\Application\Admin\UseCase\Recovery\RecoveryFinishInputData;
use Auth\Application\Admin\UseCase\Recovery\RecoveryFinishUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Support\Contracts\MapperInterface;

final readonly class RecoveryFinishController extends Controller
{
    public function __construct(
        private MapperInterface $mapper,
        private RecoveryFinishUseCase $useCase,
        private RecoveryFinishPresenter $presenter,
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        return $this->mapper->map(RecoveryFinishInputData::class, $request->all())
            |> $this->useCase->handle(...)
            |> $this->presenter->present(...);
    }
}
