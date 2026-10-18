<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\ReleaseGroup;

use DateTime;
use OpenAPI\Admin\Client\Model\AttachedTag as OpenApiAttachedTag;
use OpenAPI\Admin\Client\Model\ReleaseFormatValue;
use OpenAPI\Admin\Client\Model\ReleaseGroup as OpenApiReleaseGroup;
use OpenAPI\Admin\Client\Model\ReleaseGroupReferencedRelease as OpenApiReleaseGroupReferencedRelease;
use OpenAPI\Admin\Client\Model\ReleaseGroupSummary as OpenApiReleaseGroupSummary;
use OpenAPI\Admin\Client\Model\ReleaseGroupTypeValue;
use Release\Application\Admin\Query\ReleaseGroupReferencedRelease;
use Release\Application\Admin\Query\ReleaseGroupSummary;
use Release\Domain\Models\ReleaseGroup;
use Tag\Domain\Models\Tag;

class Converter
{
    /**
     * @param array<Tag> $tags
     */
    public function toOpenApiReleaseGroup(ReleaseGroup $releaseGroup, array $tags): OpenApiReleaseGroup
    {
        return new OpenApiReleaseGroup()
            ->setReleaseGroupId($releaseGroup->releaseGroupId->value)
            ->setTitle($releaseGroup->title->value)
            ->setType(ReleaseGroupTypeValue::from($releaseGroup->type->value))
            ->setDescription($releaseGroup->description->value)
            ->setNote($releaseGroup->note->value)
            ->setIsDisplay($releaseGroup->isDisplay)
            ->setOrderNo($releaseGroup->orderNo->value)
            ->setTags(array_values(array_map(
                static fn (Tag $tag): OpenApiAttachedTag => new OpenApiAttachedTag()->setTagId($tag->tagId->value)->setName($tag->name->value),
                $tags,
            )));
    }

    public function toOpenApiSummary(ReleaseGroupSummary $summary): OpenApiReleaseGroupSummary
    {
        return new OpenApiReleaseGroupSummary([
            'first_released_on' => $summary->firstReleasedOn === null ? null : new DateTime($summary->firstReleasedOn),
        ])
            ->setReleaseGroupId($summary->releaseGroupId)
            ->setTitle($summary->title)
            ->setType(ReleaseGroupTypeValue::from($summary->typeValue))
            ->setDescription($summary->description)
            ->setIsDisplay($summary->isDisplay)
            ->setOrderNo($summary->orderNo)
            ->setReleaseCount($summary->releaseCount)
            ->setSongCount($summary->songCount);
    }

    public function toOpenApiReferencedRelease(ReleaseGroupReferencedRelease $release): OpenApiReleaseGroupReferencedRelease
    {
        return new OpenApiReleaseGroupReferencedRelease()
            ->setReleaseId($release->releaseId)
            ->setName($release->name)
            ->setReleasedOn(new DateTime($release->releasedOn))
            ->setIsDisplay($release->isDisplay)
            ->setOrderNo($release->orderNo)
            ->setFormats(array_map(
                static fn (int $formatValue): ReleaseFormatValue => ReleaseFormatValue::from($formatValue),
                $release->formatValues,
            ));
    }
}
