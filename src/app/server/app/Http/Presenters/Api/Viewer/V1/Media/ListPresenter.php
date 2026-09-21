<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Viewer\V1\Media;

use DateTime;
use Illuminate\Http\JsonResponse;
use Media\Application\Viewer\Query\MediaListItem;
use Media\Application\Viewer\Query\MediaSongSummary;
use Media\Application\Viewer\UseCase\List\ListOutputData;
use OpenAPI\Viewer\Client\Model\MediaListItem as OpenApiMediaListItem;
use OpenAPI\Viewer\Client\Model\MediaListResponse;
use OpenAPI\Viewer\Client\Model\MediaRelationCounts;
use OpenAPI\Viewer\Client\Model\MediaSongSummary as OpenApiMediaSongSummary;
use OpenAPI\Viewer\Client\Model\MediaType;
use OpenAPI\Viewer\Client\Model\MediaTypeValue;
use OpenAPI\Viewer\Client\Model\SongType;
use OpenAPI\Viewer\Client\Model\SongTypeValue;

class ListPresenter
{
    public function present(ListOutputData $outputData): JsonResponse
    {
        return response()->json(
            new MediaListResponse(['next_cursor' => $outputData->nextCursor])
                ->setMedia(array_map($this->toOpenApiMediaListItem(...), $outputData->media)),
            200,
        );
    }

    private function toOpenApiMediaListItem(MediaListItem $media): OpenApiMediaListItem
    {
        $songs = array_map($this->toOpenApiMediaSongSummary(...), $media->songs);

        return new OpenApiMediaListItem()->setMediaId($media->mediaId)
            ->setTitle($media->title)
            ->setUrl($media->url)
            ->setPublishedAt(DateTime::createFromImmutable($media->publishedAt))
            ->setType(new MediaType()->setName($media->type->getName())->setValue(MediaTypeValue::from($media->type->value)))
            ->setCounts(new MediaRelationCounts()->setSongCount(count($songs)))
            ->setSongs($songs);
    }

    private function toOpenApiMediaSongSummary(MediaSongSummary $song): OpenApiMediaSongSummary
    {
        return new OpenApiMediaSongSummary()->setSongId($song->songId)
            ->setTitle($song->title)
            ->setType(new SongType()->setName($song->type->getName())->setValue(SongTypeValue::from($song->type->value)));
    }
}
