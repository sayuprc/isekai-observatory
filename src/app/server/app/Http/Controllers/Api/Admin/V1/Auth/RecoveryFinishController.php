<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\Auth\RecoveryFinishPresenter;
use Auth\Application\Admin\UseCase\Recovery\RecoveryFinishInputData;
use Auth\Application\Admin\UseCase\Recovery\RecoveryFinishUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecoveryFinishController extends Controller
{
    public function __construct(
        private readonly RecoveryFinishUseCase $recoveryFinishUseCase,
        private readonly RecoveryFinishPresenter $presenter,
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        /** @var array<string, mixed> $credential */
        $credential = $request->array('credential');

        $inputData = new RecoveryFinishInputData(
            $request->string('authCeremonyId')->toString(),
            $credential,
        );

        return $this->presenter->present($this->recoveryFinishUseCase->handle($inputData));
    }
}
