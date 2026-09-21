<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\SongType;

use OpenAPI\Admin\Client\Model\SongType as OpenApiSongType;
use OpenAPI\Admin\Client\Model\SongTypeValue;
use Song\Domain\Models\SongType;

class Converter
{
    public function toOpenApiSongType(SongType $type): OpenApiSongType
    {
        return new OpenApiSongType()
            ->setName($type->getName())
            ->setValue(SongTypeValue::from($type->value));
    }
}
