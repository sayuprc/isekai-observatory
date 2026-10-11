<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Person;

use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\PersonSearchResponse;
use OpenAPI\Admin\Client\Model\PersonSummary;
use Person\Application\Admin\Query\PersonUsageCount;
use Person\Application\Admin\UseCase\Search\SearchOutputData;
use Person\Domain\Models\Person;

class SearchPresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    public function present(SearchOutputData $outputData): JsonResponse
    {
        return response()->json(
            new PersonSearchResponse()
                ->setPersons(array_map(
                    fn (Person $person): PersonSummary => $this->converter->toOpenApiPersonSummary(
                        $person,
                        $outputData->usageCounts[$person->personId->value] ?? new PersonUsageCount(0, 0),
                    ),
                    $outputData->persons,
                ))
                ->setMaxPage($outputData->maxPage),
            200,
        );
    }
}
