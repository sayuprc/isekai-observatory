<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\SongTag;

use OpenAPI\Admin\Client\Model\SongTag as OpenApiSongTag;
use Song\Domain\Models\Tag\SongTag;

class Converter
{
    public function toOpenApiSongTag(SongTag $tag): OpenApiSongTag
    {
        return new OpenApiSongTag()
            ->setSongTagId($tag->songTagId->value)
            ->setName($tag->name->value)
            ->setOrderNo($tag->orderNo->value);
    }
}
