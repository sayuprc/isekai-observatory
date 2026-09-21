<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Song;

use DateTime;
use OpenAPI\Admin\Client\Model\MediaType as OpenApiMediaType;
use OpenAPI\Admin\Client\Model\MediaTypeValue;
use OpenAPI\Admin\Client\Model\Song as OpenApiSong;
use OpenAPI\Admin\Client\Model\SongAttachedTag as OpenApiSongAttachedTag;
use OpenAPI\Admin\Client\Model\SongLinkedMedia as OpenApiSongLinkedMedia;
use OpenAPI\Admin\Client\Model\SongPerson as OpenApiSongPerson;
use OpenAPI\Admin\Client\Model\SongPersonRole as OpenApiSongPersonRole;
use OpenAPI\Admin\Client\Model\SongType as OpenApiSongType;
use OpenAPI\Admin\Client\Model\SongTypeValue;
use Song\Application\Admin\Assemble\AssembledMedia;
use Song\Application\Admin\Assemble\AssembledPerson;
use Song\Application\Admin\Assemble\AssembledSong;
use Song\Application\Admin\Assemble\AssembledTag;

readonly class Converter
{
    public function toOpenApiSong(AssembledSong $song): OpenApiSong
    {
        return new OpenApiSong(['lyrics_link' => $song->lyricsLink])
            ->setSongId($song->songId)
            ->setTitle($song->title)
            ->setDescription($song->description)
            ->setType($this->toOpenApiSongType($song))
            ->setIsDisplay($song->isDisplay)
            ->setOrderNo($song->orderNo)
            ->setPersons(array_map($this->toOpenApiSongPerson(...), $song->persons))
            ->setTags(array_map($this->toOpenApiSongTag(...), $song->tags))
            ->setMedia(array_map($this->toOpenApiSongLinkedMedia(...), $song->media));
    }

    private function toOpenApiSongType(AssembledSong $song): OpenApiSongType
    {
        return new OpenApiSongType()
            ->setName($song->typeName)
            ->setValue(SongTypeValue::from($song->typeValue));
    }

    private function toOpenApiSongPerson(AssembledPerson $person): OpenApiSongPerson
    {
        return new OpenApiSongPerson()
            ->setPersonId($person->personId)
            ->setName($person->name)
            ->setRole(OpenApiSongPersonRole::from($person->role->value))
            ->setOrderNo($person->orderNo);
    }

    private function toOpenApiSongTag(AssembledTag $tag): OpenApiSongAttachedTag
    {
        return new OpenApiSongAttachedTag()
            ->setSongTagId($tag->songTagId)
            ->setName($tag->name);
    }

    private function toOpenApiSongLinkedMedia(AssembledMedia $media): OpenApiSongLinkedMedia
    {
        return new OpenApiSongLinkedMedia()
            ->setMediaId($media->mediaId)
            ->setTitle($media->title)
            ->setUrl($media->url)
            ->setPublishedAt(new DateTime($media->publishedAt))
            ->setType($this->toOpenApiMediaType($media))
            ->setIsDisplay($media->isDisplay)
            ->setOrderNo($media->orderNo);
    }

    private function toOpenApiMediaType(AssembledMedia $media): OpenApiMediaType
    {
        return new OpenApiMediaType()
            ->setName($media->typeName)
            ->setValue(MediaTypeValue::from($media->typeValue));
    }
}
