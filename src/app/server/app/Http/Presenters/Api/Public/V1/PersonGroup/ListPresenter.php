<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Public\V1\PersonGroup;

use Illuminate\Http\JsonResponse;
use OpenAPI\Public\Client\Model\PersonGroup;
use OpenAPI\Public\Client\Model\PersonGroupListResponse;
use Public\Application\Person\UseCase\Group\List\ListOutputData;
use Public\Application\Person\UseCase\Group\List\Query\PersonGroupListItem;

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
