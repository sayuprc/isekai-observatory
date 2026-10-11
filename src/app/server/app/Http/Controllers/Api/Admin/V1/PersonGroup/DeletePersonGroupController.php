<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\PersonGroup;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\PersonGroup\DeletePresenter;
use Illuminate\Http\JsonResponse;
use Person\Application\Admin\UseCase\Group\Delete\DeleteInputData;
use Person\Application\Admin\UseCase\Group\Delete\DeleteUseCase;

class DeletePersonGroupController extends Controller
{
    public function __construct(
        private readonly DeleteUseCase $useCase,
        private readonly DeletePresenter $presenter,
    ) {
    }

    public function handle(string $personGroupId): JsonResponse
    {
        $this->useCase->handle(new DeleteInputData($personGroupId));

        return $this->presenter->present();
    }
}
