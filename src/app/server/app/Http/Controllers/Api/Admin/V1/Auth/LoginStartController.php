<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\Auth\LoginStartPresenter;
use Auth\Application\Admin\UseCase\Login\LoginStartInputData;
use Auth\Application\Admin\UseCase\Login\LoginStartUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoginStartController extends Controller
{
    public function __construct(
        private readonly LoginStartUseCase $loginStartUseCase,
        private readonly LoginStartPresenter $presenter,
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        $inputData = new LoginStartInputData(
            $request->string('email')->toString(),
        );

        return $this->presenter->present($this->loginStartUseCase->handle($inputData));
    }
}
