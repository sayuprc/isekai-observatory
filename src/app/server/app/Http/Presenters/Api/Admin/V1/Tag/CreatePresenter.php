<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Tag;

use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\TagCreateResponse;
use Tag\Application\Admin\UseCase\Create\CreateOutputData;

class CreatePresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    public function present(CreateOutputData $outputData): JsonResponse
    {
        return response()->json(
            new TagCreateResponse()->setTag($this->converter->toOpenApiTag($outputData->tag)),
            200,
        );
    }
}
