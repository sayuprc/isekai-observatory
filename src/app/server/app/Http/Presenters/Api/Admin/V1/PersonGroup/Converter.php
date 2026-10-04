<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\PersonGroup;

use OpenAPI\Admin\Client\Model\PersonGroup as OpenApiPersonGroup;
use OpenAPI\Admin\Client\Model\PersonGroupMember as OpenApiPersonGroupMember;
use Person\Application\Admin\Query\PersonGroupMemberSummary;
use Person\Application\Admin\Query\PersonGroupSummary;

class Converter
{
    public function toOpenApiPersonGroup(PersonGroupSummary $personGroup): OpenApiPersonGroup
    {
        return new OpenApiPersonGroup()
            ->setPersonGroupId($personGroup->personGroupId)
            ->setName($personGroup->name)
            ->setMembers(array_map(
                static fn (PersonGroupMemberSummary $member): OpenApiPersonGroupMember => new OpenApiPersonGroupMember()
                    ->setPersonId($member->personId)
                    ->setName($member->name)
                    ->setOrderNo($member->orderNo),
                $personGroup->members,
            ));
    }
}
