<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Viewer\V1\Release;

use DateTime;
use Illuminate\Http\JsonResponse;
use OpenAPI\Viewer\Client\Model\ReleaseFormatValue;
use OpenAPI\Viewer\Client\Model\ReleaseGroupListItem as OpenApiReleaseGroupListItem;
use OpenAPI\Viewer\Client\Model\ReleaseGroupListResponse;
use OpenAPI\Viewer\Client\Model\ReleaseGroupTypeValue;
use OpenAPI\Viewer\Client\Model\ReleaseListItem as OpenApiReleaseListItem;
use OpenAPI\Viewer\Client\Model\ReleaseMediumItem as OpenApiReleaseMediumItem;
use OpenAPI\Viewer\Client\Model\ReleaseTrackItem as OpenApiReleaseTrackItem;
use Release\Application\Viewer\Query\ReleaseGroupListItem;
use Release\Application\Viewer\Query\ReleaseListItem;
use Release\Application\Viewer\Query\ReleaseMediumItem;
use Release\Application\Viewer\Query\ReleaseTrackItem;
use Release\Application\Viewer\UseCase\List\ListOutputData;
use Release\Domain\Models\ReleaseFormat;

class ListPresenter
{
    public function present(ListOutputData $outputData): JsonResponse
    {
        return response()->json(
            new ReleaseGroupListResponse(['next_cursor' => $outputData->nextCursor])
                ->setReleaseGroups(array_map($this->toOpenApiReleaseGroupListItem(...), $outputData->releaseGroups)),
            200,
        );
    }

    private function toOpenApiReleaseGroupListItem(ReleaseGroupListItem $releaseGroup): OpenApiReleaseGroupListItem
    {
        return new OpenApiReleaseGroupListItem()
            ->setReleaseGroupId($releaseGroup->releaseGroupId)
            ->setTitle($releaseGroup->title)
            ->setTypeValue(ReleaseGroupTypeValue::from($releaseGroup->type->value))
            ->setDescription($releaseGroup->description)
            ->setFirstReleasedOn(new DateTime($releaseGroup->firstReleasedOn))
            ->setReleases(array_map($this->toOpenApiReleaseListItem(...), $releaseGroup->releases));
    }

    private function toOpenApiReleaseListItem(ReleaseListItem $release): OpenApiReleaseListItem
    {
        return new OpenApiReleaseListItem()
            ->setReleaseId($release->releaseId)
            ->setName($release->name)
            ->setReleasedOn(new DateTime($release->releasedOn))
            ->setDescription($release->description)
            ->setColor($release->color)
            ->setOrderNo($release->orderNo)
            ->setFormatValues(array_map($this->toOpenApiReleaseFormatValue(...), $release->formats))
            ->setMedia(array_map($this->toOpenApiReleaseMediumItem(...), $release->media));
    }

    private function toOpenApiReleaseFormatValue(ReleaseFormat $format): ReleaseFormatValue
    {
        return ReleaseFormatValue::from($format->value);
    }

    private function toOpenApiReleaseMediumItem(ReleaseMediumItem $medium): OpenApiReleaseMediumItem
    {
        return new OpenApiReleaseMediumItem([
            'name' => $medium->name,
        ])
            ->setPosition($medium->position)
            ->setTracks(array_map($this->toOpenApiReleaseTrackItem(...), $medium->tracks));
    }

    private function toOpenApiReleaseTrackItem(ReleaseTrackItem $track): OpenApiReleaseTrackItem
    {
        return new OpenApiReleaseTrackItem([
            'song_id' => $track->songId,
        ])
            ->setTrackNo($track->trackNo)
            ->setTitle($track->title)
            ->setIsDisplay($track->isDisplay);
    }
}
