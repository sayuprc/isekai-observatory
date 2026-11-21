<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\ReleaseGroup;

use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\ReleaseGroupSearchResponse;
use Release\Application\Admin\UseCase\Group\Search\SearchOutputData;

class SearchPresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    public function present(SearchOutputData $outputData): JsonResponse
    {
        return response()->json(
            new ReleaseGroupSearchResponse()
                ->setReleaseGroups(array_map($this->converter->toOpenApiSummary(...), $outputData->releaseGroups))
                ->setMaxPage($outputData->maxPage),
            200,
        );
    }
}
