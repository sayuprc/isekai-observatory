<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\Media;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\Media\UpdatePresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Media\Application\Admin\UseCase\Update\UpdateInputData;
use Media\Application\Admin\UseCase\Update\UpdateUseCase;
use Support\Contracts\MapperInterface;

class UpdateMediaController extends Controller
{
    public function __construct(
        private readonly MapperInterface $mapper,
        private readonly UpdateUseCase $useCase,
        private readonly UpdatePresenter $presenter,
    ) {
    }

    public function handle(Request $request, string $mediaId): JsonResponse
    {
        return $this->mapper->map(
            UpdateInputData::class,
            [
                ...$request->all(),
                'mediaId' => $mediaId,
            ],
        )
            |> $this->useCase->handle(...)
            |> $this->presenter->present(...);
    }
}
