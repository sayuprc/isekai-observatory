<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\ReleaseGroup;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\ReleaseGroup\DeletePresenter;
use Illuminate\Http\JsonResponse;
use Release\Application\Admin\UseCase\Group\Delete\DeleteInputData;
use Release\Application\Admin\UseCase\Group\Delete\DeleteUseCase;

class DeleteReleaseGroupController extends Controller
{
    public function __construct(
        private readonly DeleteUseCase $useCase,
        private readonly DeletePresenter $presenter,
    ) {
    }

    public function handle(string $releaseGroupId): JsonResponse
    {
        $this->useCase->handle(new DeleteInputData($releaseGroupId));

        return $this->presenter->present();
    }
}
