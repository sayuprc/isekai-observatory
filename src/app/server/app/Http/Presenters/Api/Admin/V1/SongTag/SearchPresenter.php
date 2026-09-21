<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\SongTag;

use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\SongTagSearchResponse;
use Song\Application\Admin\UseCase\Tag\Search\SearchOutputData;

class SearchPresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    public function present(SearchOutputData $outputData): JsonResponse
    {
        return response()->json(
            new SongTagSearchResponse()
                ->setTags(array_map($this->converter->toOpenApiSongTag(...), $outputData->tags))
                ->setMaxPage($outputData->maxPage),
            200,
        );
    }
}
