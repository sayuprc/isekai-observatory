<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\Auth\RecoveryStartPresenter;
use Auth\Application\Admin\UseCase\Recovery\RecoveryStartInputData;
use Auth\Application\Admin\UseCase\Recovery\RecoveryStartUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Support\Contracts\MapperInterface;

final readonly class RecoveryStartController extends Controller
{
    public function __construct(
        private MapperInterface $mapper,
        private RecoveryStartUseCase $useCase,
        private RecoveryStartPresenter $presenter,
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        return $this->mapper->map(RecoveryStartInputData::class, $request->all())
            |> $this->useCase->handle(...)
            |> $this->presenter->present(...);
    }
}
