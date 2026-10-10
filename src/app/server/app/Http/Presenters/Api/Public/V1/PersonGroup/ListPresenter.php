<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Public\V1\PersonGroup;

use Illuminate\Http\JsonResponse;
use OpenAPI\Public\Client\Model\PersonGroup;
use OpenAPI\Public\Client\Model\PersonGroupListResponse;
use Person\Application\Public\Query\PersonGroupListItem;
use Person\Application\Public\UseCase\Group\List\ListOutputData;

class ListPresenter
{
    public function present(ListOutputData $outputData): JsonResponse
    {
        return response()->json(
            new PersonGroupListResponse(['next_page_token' => $outputData->nextPageToken])
                ->setItems(array_map($this->toOpenApiPersonGroup(...), $outputData->personGroups)),
            200,
        );
    }

    private function toOpenApiPersonGroup(PersonGroupListItem $personGroup): PersonGroup
    {
        return new PersonGroup()
            ->setPersonGroupId($personGroup->personGroupId)
            ->setName($personGroup->name)
            ->setMemberIds($personGroup->memberIds);
    }
}
