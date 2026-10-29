<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Public\V1\Tag;

use Illuminate\Http\JsonResponse;
use OpenAPI\Public\Client\Model\Tag;
use OpenAPI\Public\Client\Model\TagListResponse;
use Tag\Application\Public\Query\TagListItem;
use Tag\Application\Public\UseCase\List\ListOutputData;

class ListPresenter
{
    public function present(ListOutputData $outputData): JsonResponse
    {
        return response()->json(
            new TagListResponse(['next_page_token' => $outputData->nextPageToken])
                ->setItems(array_map($this->toOpenApiTag(...), $outputData->tags)),
            200,
        );
    }

    private function toOpenApiTag(TagListItem $tag): Tag
    {
        return new Tag()->setTagId($tag->tagId)
            ->setName($tag->name);
    }
}
