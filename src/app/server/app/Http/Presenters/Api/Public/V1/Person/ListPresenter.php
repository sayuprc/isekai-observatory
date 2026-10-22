<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Public\V1\Person;

use Illuminate\Http\JsonResponse;
use OpenAPI\Public\Client\Model\Person;
use OpenAPI\Public\Client\Model\PersonListResponse;
use Person\Application\Public\Query\PersonListItem;
use Person\Application\Public\UseCase\List\ListOutputData;

class ListPresenter
{
    public function present(ListOutputData $outputData): JsonResponse
    {
        return response()->json(
            new PersonListResponse(['next_page_token' => $outputData->nextPageToken])
                ->setItems(array_map($this->toOpenApiPerson(...), $outputData->persons)),
            200,
        );
    }

    private function toOpenApiPerson(PersonListItem $person): Person
    {
        return new Person()
            ->setPersonId($person->personId)
            ->setName($person->name);
    }
}
