<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Tag;

use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\TagSearchResponse;
use OpenAPI\Admin\Client\Model\TagSummary;
use Tag\Application\Admin\Query\TagUsageCount;
use Tag\Application\Admin\UseCase\Search\SearchOutputData;
use Tag\Domain\Models\Tag;

class SearchPresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    public function present(SearchOutputData $outputData): JsonResponse
    {
        return response()->json(
            new TagSearchResponse()
                ->setTags(array_map(
                    fn (Tag $tag): TagSummary => $this->converter->toOpenApiTagSummary(
                        $tag,
                        $outputData->usageCounts[$tag->tagId->value] ?? new TagUsageCount(0, 0, 0, 0),
                    ),
                    $outputData->tags,
                ))
                ->setMaxPage($outputData->maxPage),
            200,
        );
    }
}
