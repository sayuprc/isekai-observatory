<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Media;

use DateTime;
use Media\Application\Admin\Query\MediaReferencedSong;
use Media\Application\Admin\Query\MediaUsageCount;
use Media\Domain\Models\Media;
use OpenAPI\Admin\Client\Model\AttachedTag as OpenApiAttachedTag;
use OpenAPI\Admin\Client\Model\Media as OpenApiMedia;
use OpenAPI\Admin\Client\Model\MediaReferencedSong as OpenApiMediaReferencedSong;
use OpenAPI\Admin\Client\Model\MediaSummary as OpenApiMediaSummary;
use OpenAPI\Admin\Client\Model\MediaTypeValue;
use Tag\Domain\Models\Tag;

class Converter
{
    /**
     * @param array<Tag> $tags
     *
     * @return array<int, OpenApiAttachedTag>
     */
    public function toOpenApiTags(array $tags): array
    {
        return array_values(array_map(
            static fn (Tag $tag): OpenApiAttachedTag => new OpenApiAttachedTag()->setTagId($tag->tagId->value)
                ->setName($tag->name->value),
            $tags,
        ));
    }

    public function toOpenApiMedia(Media $media): OpenApiMedia
    {
        return new OpenApiMedia()->setMediaId($media->mediaId->value)
            ->setTitle($media->title->value)
            ->setUrl($media->url->value)
            ->setPublishedAt(DateTime::createFromImmutable($media->publishedAt->value))
            ->setType(MediaTypeValue::from($media->type->value))
            ->setIsDisplay($media->isDisplay);
    }

    public function toOpenApiMediaSummary(Media $media, MediaUsageCount $usage): OpenApiMediaSummary
    {
        return new OpenApiMediaSummary()->setMediaId($media->mediaId->value)
            ->setTitle($media->title->value)
            ->setUrl($media->url->value)
            ->setPublishedAt(DateTime::createFromImmutable($media->publishedAt->value))
            ->setType(MediaTypeValue::from($media->type->value))
            ->setIsDisplay($media->isDisplay)
            ->setSongCount($usage->songCount)
            ->setEventCount($usage->eventCount);
    }

    public function toOpenApiReferencedSong(MediaReferencedSong $song): OpenApiMediaReferencedSong
    {
        return new OpenApiMediaReferencedSong()->setSongId($song->songId)
            ->setTitle($song->title)
            ->setSongOrderNo($song->songOrderNo)
            ->setMediaOrderNo($song->mediaOrderNo);
    }
}
