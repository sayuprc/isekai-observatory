<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Media;

use Illuminate\Http\JsonResponse;
use Media\Application\Admin\UseCase\Update\UpdateOutputData;
use OpenAPI\Admin\Client\Model\MediaUpdateResponse;

class UpdatePresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    public function present(UpdateOutputData $outputData): JsonResponse
    {
        return response()->json(
            new MediaUpdateResponse()->setMedia($this->converter->toOpenApiMedia($outputData->media)),
            200,
        );
    }
}
