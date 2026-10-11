<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\Auth\GenerateRecoveryCodesPresenter;
use Auth\Application\Admin\UseCase\RecoveryCode\Generate\GenerateRecoveryCodesUseCase;
use Illuminate\Http\JsonResponse;

class GenerateRecoveryCodesController extends Controller
{
    public function __construct(
        private readonly GenerateRecoveryCodesUseCase $generateRecoveryCodesUseCase,
        private readonly GenerateRecoveryCodesPresenter $presenter,
    ) {
    }

    public function handle(): JsonResponse
    {
        return $this->presenter->present($this->generateRecoveryCodesUseCase->handle());
    }
}
