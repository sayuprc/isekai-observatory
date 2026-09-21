<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Media;

use DateTime;
use Media\Application\Admin\Query\MediaReferencedSong;
use Media\Domain\Models\Media;
use OpenAPI\Admin\Client\Model\Media as OpenApiMedia;
use OpenAPI\Admin\Client\Model\MediaReferencedSong as OpenApiMediaReferencedSong;
use OpenAPI\Admin\Client\Model\MediaType as OpenApiMediaType;
use OpenAPI\Admin\Client\Model\MediaTypeValue;

class Converter
{
    public function toOpenApiMedia(Media $media): OpenApiMedia
    {
        return new OpenApiMedia()
            ->setMediaId($media->mediaId->value)
            ->setTitle($media->title->value)
            ->setUrl($media->url->value)
            ->setPublishedAt(DateTime::createFromImmutable($media->publishedAt->value))
            ->setType($this->toOpenApiMediaType($media))
            ->setIsDisplay($media->isDisplay);
    }

    public function toOpenApiReferencedSong(MediaReferencedSong $song): OpenApiMediaReferencedSong
    {
        return new OpenApiMediaReferencedSong()
            ->setSongId($song->songId)
            ->setTitle($song->title)
            ->setSongOrderNo($song->songOrderNo)
            ->setMediaOrderNo($song->mediaOrderNo);
    }

    private function toOpenApiMediaType(Media $media): OpenApiMediaType
    {
        return new OpenApiMediaType()
            ->setName($media->type->getName())
            ->setValue(MediaTypeValue::from($media->type->value));
    }
}
