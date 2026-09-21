<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\ReleaseGroup;

use DateTime;
use OpenAPI\Admin\Client\Model\ReleaseFormatValue;
use OpenAPI\Admin\Client\Model\ReleaseGroup as OpenApiReleaseGroup;
use OpenAPI\Admin\Client\Model\ReleaseGroupReferencedRelease as OpenApiReleaseGroupReferencedRelease;
use OpenAPI\Admin\Client\Model\ReleaseGroupSummary as OpenApiReleaseGroupSummary;
use OpenAPI\Admin\Client\Model\ReleaseGroupTypeValue;
use Release\Application\Admin\Query\ReleaseGroupReferencedRelease;
use Release\Application\Admin\Query\ReleaseGroupSummary;
use Release\Domain\Models\ReleaseGroup;

class Converter
{
    public function toOpenApiReleaseGroup(ReleaseGroup $releaseGroup): OpenApiReleaseGroup
    {
        return new OpenApiReleaseGroup()
            ->setReleaseGroupId($releaseGroup->releaseGroupId->value)
            ->setTitle($releaseGroup->title->value)
            ->setTypeValue(ReleaseGroupTypeValue::from($releaseGroup->type->value))
            ->setDescription($releaseGroup->description->value)
            ->setIsDisplay($releaseGroup->isDisplay)
            ->setOrderNo($releaseGroup->orderNo->value);
    }

    public function toOpenApiSummary(ReleaseGroupSummary $summary): OpenApiReleaseGroupSummary
    {
        return new OpenApiReleaseGroupSummary([
            'first_released_on' => is_null($summary->firstReleasedOn) ? null : new DateTime($summary->firstReleasedOn),
        ])
            ->setReleaseGroupId($summary->releaseGroupId)
            ->setTitle($summary->title)
            ->setTypeValue(ReleaseGroupTypeValue::from($summary->typeValue))
            ->setDescription($summary->description)
            ->setIsDisplay($summary->isDisplay)
            ->setOrderNo($summary->orderNo);
    }

    public function toOpenApiReferencedRelease(ReleaseGroupReferencedRelease $release): OpenApiReleaseGroupReferencedRelease
    {
        return new OpenApiReleaseGroupReferencedRelease()
            ->setReleaseId($release->releaseId)
            ->setName($release->name)
            ->setReleasedOn(new DateTime($release->releasedOn))
            ->setColor($release->color)
            ->setIsDisplay($release->isDisplay)
            ->setOrderNo($release->orderNo)
            ->setFormatValues(array_map(
                static fn (int $formatValue): ReleaseFormatValue => ReleaseFormatValue::from($formatValue),
                $release->formatValues,
            ));
    }
}
