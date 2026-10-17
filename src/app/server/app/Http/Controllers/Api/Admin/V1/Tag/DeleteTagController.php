<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\Tag;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\Tag\DeletePresenter;
use Illuminate\Http\JsonResponse;
use Tag\Application\Admin\UseCase\Delete\DeleteInputData;
use Tag\Application\Admin\UseCase\Delete\DeleteUseCase;

class DeleteTagController extends Controller
{
    public function __construct(
        private readonly DeleteUseCase $useCase,
        private readonly DeletePresenter $presenter,
    ) {
    }

    public function handle(string $tagId): JsonResponse
    {
        $this->useCase->handle(new DeleteInputData($tagId));

        return $this->presenter->present();
    }
}
