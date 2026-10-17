<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Tag;

use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\TagListResponse;
use Tag\Application\Admin\UseCase\List\ListOutputData;

class ListPresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    public function present(ListOutputData $outputData): JsonResponse
    {
        return response()->json(
            new TagListResponse()->setTags(
                array_map(
                    $this->converter->toOpenApiTag(...),
                    $outputData->tags,
                ),
            ),
            200,
        );
    }
}
