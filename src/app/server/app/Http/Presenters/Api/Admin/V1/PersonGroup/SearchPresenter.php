<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\PersonGroup;

use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\PersonGroupSearchResponse;
use OpenAPI\Admin\Client\Model\PersonGroupSummary as OpenApiPersonGroupSummary;
use Person\Application\Admin\Query\PersonGroupSummary;
use Person\Application\Admin\UseCase\Group\Search\SearchOutputData;

class SearchPresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    public function present(SearchOutputData $outputData): JsonResponse
    {
        return response()->json(
            new PersonGroupSearchResponse()
                ->setPersonGroups(array_map(
                    fn (PersonGroupSummary $personGroup): OpenApiPersonGroupSummary => $this->converter->toOpenApiPersonGroupSummary(
                        $personGroup,
                        $outputData->usageCounts[$personGroup->personGroupId] ?? 0,
                    ),
                    $outputData->personGroups,
                ))
                ->setMaxPage($outputData->maxPage),
            200,
        );
    }
}
