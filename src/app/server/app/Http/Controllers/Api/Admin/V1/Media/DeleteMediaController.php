<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\Media;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\Media\DeletePresenter;
use Illuminate\Http\JsonResponse;
use Media\Application\Admin\UseCase\Delete\DeleteInputData;
use Media\Application\Admin\UseCase\Delete\DeleteUseCase;

class DeleteMediaController extends Controller
{
    public function __construct(
        private readonly DeleteUseCase $useCase,
        private readonly DeletePresenter $presenter,
    ) {
    }

    public function handle(string $mediaId): JsonResponse
    {
        $this->useCase->handle(new DeleteInputData($mediaId));

        return $this->presenter->present();
    }
}
