<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Public\V1\ReleaseGroup;

use DateTime;
use Illuminate\Http\JsonResponse;
use OpenAPI\Public\Client\Model\Release;
use OpenAPI\Public\Client\Model\ReleaseFormatValue;
use OpenAPI\Public\Client\Model\ReleaseGroup;
use OpenAPI\Public\Client\Model\ReleaseGroupListResponse;
use OpenAPI\Public\Client\Model\ReleaseGroupTypeValue;
use OpenAPI\Public\Client\Model\ReleaseMedium;
use OpenAPI\Public\Client\Model\ReleaseTrack;
use Release\Application\Public\Query\ReleaseGroupListItem;
use Release\Application\Public\Query\ReleaseItem;
use Release\Application\Public\Query\ReleaseMediumItem;
use Release\Application\Public\Query\ReleaseTrackItem;
use Release\Application\Public\UseCase\List\ListOutputData;
use Release\Domain\Models\ReleaseFormat;

class ListPresenter
{
    public function present(ListOutputData $outputData): JsonResponse
    {
        return response()->json(
            new ReleaseGroupListResponse(['next_page_token' => $outputData->nextPageToken])
                ->setItems(array_map($this->toOpenApiReleaseGroup(...), $outputData->releaseGroups)),
            200,
        );
    }

    private function toOpenApiReleaseGroup(ReleaseGroupListItem $releaseGroup): ReleaseGroup
    {
        return new ReleaseGroup()
            ->setReleaseGroupId($releaseGroup->releaseGroupId)
            ->setTitle($releaseGroup->title)
            ->setType(ReleaseGroupTypeValue::from($releaseGroup->type->value))
            ->setDescription($releaseGroup->description)
            ->setReleases(array_map($this->toOpenApiRelease(...), $releaseGroup->releases));
    }

    private function toOpenApiRelease(ReleaseItem $release): Release
    {
        return new Release()
            ->setReleaseId($release->releaseId)
            ->setName($release->name)
            ->setReleasedOn(new DateTime($release->releasedOn))
            ->setDescription($release->description)
            ->setColor($release->color)
            ->setFormats(array_map(static fn (ReleaseFormat $format): ReleaseFormatValue => ReleaseFormatValue::from($format->value), $release->formats))
            ->setMedia(array_map($this->toOpenApiMedium(...), $release->media));
    }

    private function toOpenApiMedium(ReleaseMediumItem $medium): ReleaseMedium
    {
        return new ReleaseMedium([
            'position' => $medium->position,
            'name' => $medium->name,
            'tracks' => array_map($this->toOpenApiTrack(...), $medium->tracks),
        ]);
    }

    private function toOpenApiTrack(ReleaseTrackItem $track): ReleaseTrack
    {
        return new ReleaseTrack([
            'track_no' => $track->trackNo,
            'song_id' => $track->songId,
            'title' => $track->title,
        ]);
    }
}
