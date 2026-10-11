<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\Media;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\Media\GetPresenter;
use Illuminate\Http\JsonResponse;
use Media\Application\Admin\UseCase\Get\GetInputData;
use Media\Application\Admin\UseCase\Get\GetUseCase;

class GetMediaController extends Controller
{
    public function __construct(
        private readonly GetUseCase $useCase,
        private readonly GetPresenter $presenter,
    ) {
    }

    public function handle(string $mediaId): JsonResponse
    {
        return $this->presenter->present($this->useCase->handle(new GetInputData($mediaId)));
    }
}
