<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Media;

use Illuminate\Http\JsonResponse;
use Media\Application\Admin\UseCase\Create\CreateOutputData;
use OpenAPI\Admin\Client\Model\MediaCreateResponse;

class CreatePresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    public function present(CreateOutputData $outputData): JsonResponse
    {
        return response()->json(
            new MediaCreateResponse()->setMedia($this->converter->toOpenApiMedia($outputData->media)),
            200,
        );
    }
}
