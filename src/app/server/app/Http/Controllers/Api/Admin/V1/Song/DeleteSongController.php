<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\Song;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\Song\DeletePresenter;
use Illuminate\Http\JsonResponse;
use Song\Application\Admin\UseCase\Delete\DeleteInputData;
use Song\Application\Admin\UseCase\Delete\DeleteUseCase;

class DeleteSongController extends Controller
{
    public function __construct(
        private readonly DeleteUseCase $useCase,
        private readonly DeletePresenter $presenter,
    ) {
    }

    public function handle(string $songId): JsonResponse
    {
        $this->useCase->handle(new DeleteInputData($songId));

        return $this->presenter->present();
    }
}
