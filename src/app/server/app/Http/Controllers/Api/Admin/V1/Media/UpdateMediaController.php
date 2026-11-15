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

final readonly class UpdateMediaController extends Controller
{
    public function __construct(
        private MapperInterface $mapper,
        private UpdateUseCase $useCase,
        private UpdatePresenter $presenter,
    ) {
    }

    public function handle(Request $request, string $mediaId): JsonResponse
    {
        return $this->mapper->map(UpdateInputData::class, [...$request->all(), 'mediaId' => $mediaId])
            |> $this->useCase->handle(...)
            |> $this->presenter->present(...);
    }
}
