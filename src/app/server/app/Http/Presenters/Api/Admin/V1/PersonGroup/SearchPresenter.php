<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\PersonGroup;

use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\PersonGroupSearchResponse;
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
                ->setPersonGroups(array_map($this->converter->toOpenApiPersonGroup(...), $outputData->personGroups))
                ->setMaxPage($outputData->maxPage),
            200,
        );
    }
}
