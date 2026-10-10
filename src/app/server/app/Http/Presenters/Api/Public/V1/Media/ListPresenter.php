<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Public\V1\Media;

use DateTime;
use Illuminate\Http\JsonResponse;
use Media\Application\Public\Query\MediaListItem;
use Media\Application\Public\UseCase\List\ListOutputData;
use OpenAPI\Public\Client\Model\Media;
use OpenAPI\Public\Client\Model\MediaListResponse;
use OpenAPI\Public\Client\Model\MediaTypeValue;

class ListPresenter
{
    public function present(ListOutputData $outputData): JsonResponse
    {
        return response()->json(
            new MediaListResponse(['next_page_token' => $outputData->nextPageToken])
                ->setItems(array_map($this->toOpenApiMedia(...), $outputData->media)),
            200,
        );
    }

    private function toOpenApiMedia(MediaListItem $media): Media
    {
        return new Media()
            ->setMediaId($media->mediaId)
            ->setTitle($media->title)
            ->setUrl($media->url)
            ->setPublishedAt(DateTime::createFromImmutable($media->publishedAt))
            ->setType(MediaTypeValue::from($media->type->value));
    }
}
