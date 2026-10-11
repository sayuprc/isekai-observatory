<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\Person;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\Person\DeletePresenter;
use Illuminate\Http\JsonResponse;
use Person\Application\Admin\UseCase\Delete\DeleteInputData;
use Person\Application\Admin\UseCase\Delete\DeleteUseCase;

class DeletePersonController extends Controller
{
    public function __construct(
        private readonly DeleteUseCase $useCase,
        private readonly DeletePresenter $presenter,
    ) {
    }

    public function handle(string $personId): JsonResponse
    {
        $this->useCase->handle(new DeleteInputData($personId));

        return $this->presenter->present();
    }
}
