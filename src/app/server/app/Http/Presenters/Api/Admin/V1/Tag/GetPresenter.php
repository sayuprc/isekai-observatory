<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Tag;

use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\TagGetResponse;
use Tag\Application\Admin\UseCase\Get\GetOutputData;

class GetPresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    public function present(GetOutputData $outputData): JsonResponse
    {
        return response()->json(
            new TagGetResponse()->setTag($this->converter->toOpenApiTag($outputData->tag)),
            200,
        );
    }
}
