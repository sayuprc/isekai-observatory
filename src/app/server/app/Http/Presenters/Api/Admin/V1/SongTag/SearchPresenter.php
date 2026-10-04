<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\SongTag;

use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\SongTagSearchResponse;
use OpenAPI\Admin\Client\Model\SongTagSummary;
use Song\Application\Admin\UseCase\Tag\Search\SearchOutputData;
use Song\Domain\Models\Tag\SongTag;

class SearchPresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    public function present(SearchOutputData $outputData): JsonResponse
    {
        return response()->json(
            new SongTagSearchResponse()
                ->setTags(array_map(
                    fn (SongTag $tag): SongTagSummary => $this->converter->toOpenApiSongTagSummary(
                        $tag,
                        $outputData->usageCounts[$tag->songTagId->value] ?? 0,
                    ),
                    $outputData->tags,
                ))
                ->setMaxPage($outputData->maxPage),
            200,
        );
    }
}
