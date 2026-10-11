<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Person;

use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\PersonListResponse;
use Person\Application\Admin\UseCase\List\ListOutputData;

class ListPresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    public function present(ListOutputData $outputData): JsonResponse
    {
        return response()->json(
            new PersonListResponse()->setPersons(array_map($this->converter->toOpenApiPerson(...), $outputData->persons)),
            200,
        );
    }
}
