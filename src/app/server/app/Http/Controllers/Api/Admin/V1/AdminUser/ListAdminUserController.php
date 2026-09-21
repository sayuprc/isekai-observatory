<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\AdminUser;

use AdminUser\Application\Admin\UseCase\List\ListUseCase;
use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\AdminUser\ListPresenter;
use Illuminate\Http\JsonResponse;

class ListAdminUserController extends Controller
{
    public function __construct(
        private readonly ListUseCase $useCase,
        private readonly ListPresenter $presenter,
    ) {
    }

    public function handle(): JsonResponse
    {
        return $this->presenter->present($this->useCase->handle());
    }
}
