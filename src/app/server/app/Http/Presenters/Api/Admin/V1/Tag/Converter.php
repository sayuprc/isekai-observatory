<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Tag;

use OpenAPI\Admin\Client\Model\Tag as OpenApiTag;
use OpenAPI\Admin\Client\Model\TagSummary as OpenApiTagSummary;
use Tag\Application\Admin\Query\TagUsageCount;
use Tag\Domain\Models\Tag;

class Converter
{
    public function toOpenApiTagSummary(Tag $tag, TagUsageCount $usage): OpenApiTagSummary
    {
        return new OpenApiTagSummary()->setTagId($tag->tagId->value)
            ->setName($tag->name->value)
            ->setOrderNo($tag->orderNo->value)
            ->setSongCount($usage->songCount)
            ->setEventCount($usage->eventCount)
            ->setReleaseGroupCount($usage->releaseGroupCount)
            ->setMediaCount($usage->mediaCount);
    }

    public function toOpenApiTag(Tag $tag): OpenApiTag
    {
        return new OpenApiTag()->setTagId($tag->tagId->value)
            ->setName($tag->name->value)
            ->setOrderNo($tag->orderNo->value);
    }
}
