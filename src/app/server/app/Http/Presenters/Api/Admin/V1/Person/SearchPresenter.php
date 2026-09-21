<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Person;

use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\PersonSearchResponse;
use Person\Application\Admin\UseCase\Search\SearchOutputData;

class SearchPresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    public function present(SearchOutputData $outputData): JsonResponse
    {
        return response()->json(
            new PersonSearchResponse()
                ->setPersons(array_map($this->converter->toOpenApiPerson(...), $outputData->persons))
                ->setMaxPage($outputData->maxPage),
            200,
        );
    }
}
