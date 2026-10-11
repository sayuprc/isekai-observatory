<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\PersonGroup;

use OpenAPI\Admin\Client\Model\PersonGroup as OpenApiPersonGroup;
use OpenAPI\Admin\Client\Model\PersonGroupMember as OpenApiPersonGroupMember;
use OpenAPI\Admin\Client\Model\PersonGroupSummary as OpenApiPersonGroupSummary;
use Person\Application\Admin\Query\PersonGroupMemberSummary;
use Person\Application\Admin\Query\PersonGroupSummary;

class Converter
{
    public function toOpenApiPersonGroupSummary(PersonGroupSummary $personGroup, int $performanceCount): OpenApiPersonGroupSummary
    {
        return new OpenApiPersonGroupSummary()
            ->setPersonGroupId($personGroup->personGroupId)
            ->setName($personGroup->name)
            ->setMembers(array_map($this->toOpenApiMember(...), $personGroup->members))
            ->setPerformanceCount($performanceCount);
    }

    public function toOpenApiPersonGroup(PersonGroupSummary $personGroup): OpenApiPersonGroup
    {
        return new OpenApiPersonGroup()
            ->setPersonGroupId($personGroup->personGroupId)
            ->setName($personGroup->name)
            ->setMembers(array_map($this->toOpenApiMember(...), $personGroup->members));
    }

    private function toOpenApiMember(PersonGroupMemberSummary $member): OpenApiPersonGroupMember
    {
        return new OpenApiPersonGroupMember()
            ->setPersonId($member->personId)
            ->setName($member->name)
            ->setOrderNo($member->orderNo);
    }
}
