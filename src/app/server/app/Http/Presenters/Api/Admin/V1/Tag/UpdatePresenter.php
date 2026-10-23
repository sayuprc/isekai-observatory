<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Tag;

use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\TagUpdateResponse;
use Tag\Application\Admin\UseCase\Update\UpdateOutputData;

class UpdatePresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    public function present(UpdateOutputData $outputData): JsonResponse
    {
        return response()->json(
            new TagUpdateResponse()->setTag($this->converter->toOpenApiTag($outputData->tag)),
            200,
        );
    }
}
