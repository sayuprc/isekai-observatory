<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\Auth\LoginFinishPresenter;
use Auth\Application\Admin\UseCase\Login\LoginFinishInputData;
use Auth\Application\Admin\UseCase\Login\LoginFinishUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoginFinishController extends Controller
{
    public function __construct(
        private readonly LoginFinishUseCase $loginFinishUseCase,
        private readonly LoginFinishPresenter $presenter,
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        /** @var array<string, mixed> $credential */
        $credential = $request->array('credential');

        $inputData = new LoginFinishInputData(
            $request->string('authCeremonyId')->toString(),
            $credential,
        );

        return $this->presenter->present($this->loginFinishUseCase->handle($inputData));
    }
}
