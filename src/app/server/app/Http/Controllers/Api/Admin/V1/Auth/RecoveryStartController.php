<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\Auth\RecoveryStartPresenter;
use Auth\Application\Admin\UseCase\Recovery\RecoveryStartInputData;
use Auth\Application\Admin\UseCase\Recovery\RecoveryStartUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecoveryStartController extends Controller
{
    public function __construct(
        private readonly RecoveryStartUseCase $recoveryStartUseCase,
        private readonly RecoveryStartPresenter $presenter,
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        $inputData = new RecoveryStartInputData(
            $request->string('email')->toString(),
            $request->string('recoveryCode')->toString(),
            $request->string('name')->toString(),
        );

        return $this->presenter->present($this->recoveryStartUseCase->handle($inputData));
    }
}
