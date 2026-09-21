<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Viewer\V1\Song;

use DateTime;
use Illuminate\Http\JsonResponse;
use OpenAPI\Viewer\Client\Model\MediaType;
use OpenAPI\Viewer\Client\Model\MediaTypeValue;
use OpenAPI\Viewer\Client\Model\ReleaseGroupType as OpenApiReleaseGroupType;
use OpenAPI\Viewer\Client\Model\ReleaseGroupTypeValue;
use OpenAPI\Viewer\Client\Model\SongListItem as OpenApiSongListItem;
use OpenAPI\Viewer\Client\Model\SongListResponse;
use OpenAPI\Viewer\Client\Model\SongMediaSummary as OpenApiSongMediaSummary;
use OpenAPI\Viewer\Client\Model\SongRelationCounts;
use OpenAPI\Viewer\Client\Model\SongReleaseGroupSummary as OpenApiSongReleaseGroupSummary;
use OpenAPI\Viewer\Client\Model\SongType;
use OpenAPI\Viewer\Client\Model\SongTypeValue;
use Release\Domain\Models\ReleaseGroupType;
use Song\Application\Viewer\Query\SongListItem;
use Song\Application\Viewer\Query\SongMediaSummary;
use Song\Application\Viewer\Query\SongReleaseGroupSummary;
use Song\Application\Viewer\UseCase\List\ListOutputData;

class ListPresenter
{
    public function present(ListOutputData $outputData): JsonResponse
    {
        return response()->json(
            new SongListResponse(['next_cursor' => $outputData->nextCursor])
                ->setSongs(array_map($this->toOpenApiSongListItem(...), $outputData->songs)),
            200,
        );
    }

    private function toOpenApiSongListItem(SongListItem $song): OpenApiSongListItem
    {
        $media = array_map($this->toOpenApiSongMediaSummary(...), $song->media);
        $releaseGroups = array_map($this->toOpenApiSongReleaseGroupSummary(...), $song->releaseGroups);

        return new OpenApiSongListItem()
            ->setSongId($song->songId)
            ->setTitle($song->title)
            ->setDescription($song->description)
            ->setType(new SongType()->setName($song->type->getName())->setValue(SongTypeValue::from($song->type->value)))
            ->setLyricists($song->lyricists)
            ->setComposers($song->composers)
            ->setArrangers($song->arrangers)
            ->setCounts(
                new SongRelationCounts()
                    ->setReleaseCount(count($releaseGroups))
                    ->setMediaCount(count($media)),
            )
            ->setMedia($media)
            ->setReleaseGroups($releaseGroups);
    }

    private function toOpenApiSongReleaseGroupSummary(SongReleaseGroupSummary $releaseGroup): OpenApiSongReleaseGroupSummary
    {
        $type = ReleaseGroupType::from($releaseGroup->typeValue);

        return new OpenApiSongReleaseGroupSummary()
            ->setReleaseGroupId($releaseGroup->releaseGroupId)
            ->setTitle($releaseGroup->title)
            ->setType(new OpenApiReleaseGroupType()->setName($type->getName())->setValue(ReleaseGroupTypeValue::from($type->value)))
            ->setFirstReleasedOn(new DateTime($releaseGroup->firstReleasedOn))
            ->setColor($releaseGroup->color);
    }

    private function toOpenApiSongMediaSummary(SongMediaSummary $media): OpenApiSongMediaSummary
    {
        return new OpenApiSongMediaSummary()
            ->setMediaId($media->mediaId)
            ->setTitle($media->title)
            ->setType(new MediaType()->setName($media->type->getName())->setValue(MediaTypeValue::from($media->type->value)))
            ->setUrl($media->url)
            ->setPublishedAt(DateTime::createFromImmutable($media->publishedAt));
    }
}
