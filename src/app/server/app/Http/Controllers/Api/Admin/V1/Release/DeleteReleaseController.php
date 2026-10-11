<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\Release;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\Release\DeletePresenter;
use Illuminate\Http\JsonResponse;
use Release\Application\Admin\UseCase\Delete\DeleteInputData;
use Release\Application\Admin\UseCase\Delete\DeleteUseCase;

class DeleteReleaseController extends Controller
{
    public function __construct(
        private readonly DeleteUseCase $useCase,
        private readonly DeletePresenter $presenter,
    ) {
    }

    public function handle(string $releaseId): JsonResponse
    {
        $this->useCase->handle(new DeleteInputData($releaseId));

        return $this->presenter->present();
    }
}
