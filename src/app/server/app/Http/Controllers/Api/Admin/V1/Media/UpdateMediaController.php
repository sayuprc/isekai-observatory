<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\Media;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\Media\UpdatePresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Media\Application\Admin\UseCase\Update\UpdateInputData;
use Media\Application\Admin\UseCase\Update\UpdateUseCase;

class UpdateMediaController extends Controller
{
    public function __construct(
        private readonly UpdateUseCase $useCase,
        private readonly UpdatePresenter $presenter,
    ) {
    }

    public function handle(Request $request, string $mediaId): JsonResponse
    {
        $inputData = new UpdateInputData(
            $mediaId,
            $request->string('title')->toString(),
            $request->string('url')->toString(),
            $request->string('publishedAt')->toString(),
            $request->integer('typeValue'),
            $request->boolean('isDisplay'),
        );

        return $this->presenter->present($this->useCase->handle($inputData));
    }
}
