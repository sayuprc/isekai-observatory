<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\SongTag;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\SongTag\DeletePresenter;
use Illuminate\Http\JsonResponse;
use Song\Application\Admin\UseCase\Tag\Delete\DeleteInputData;
use Song\Application\Admin\UseCase\Tag\Delete\DeleteUseCase;

class DeleteSongTagController extends Controller
{
    public function __construct(
        private readonly DeleteUseCase $useCase,
        private readonly DeletePresenter $presenter,
    ) {
    }

    public function handle(string $songTagId): JsonResponse
    {
        $this->useCase->handle(new DeleteInputData($songTagId));

        return $this->presenter->present();
    }
}
