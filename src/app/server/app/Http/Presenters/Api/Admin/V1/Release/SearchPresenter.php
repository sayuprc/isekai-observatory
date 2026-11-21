<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Release;

use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\ReleaseSearchResponse;
use Release\Application\Admin\Search\SearchOutputData;

class SearchPresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    public function present(SearchOutputData $outputData): JsonResponse
    {
        return response()->json(
            new ReleaseSearchResponse()
                ->setReleases(array_map($this->converter->toOpenApiSummary(...), $outputData->releases))
                ->setMaxPage($outputData->maxPage),
            200,
        );
    }
}
