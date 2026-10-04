<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Media;

use Illuminate\Http\JsonResponse;
use Media\Application\Admin\Query\MediaUsageCount;
use Media\Application\Admin\UseCase\Search\SearchOutputData;
use Media\Domain\Models\Media;
use OpenAPI\Admin\Client\Model\MediaSearchResponse;
use OpenAPI\Admin\Client\Model\MediaSummary;

class SearchPresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    public function present(SearchOutputData $outputData): JsonResponse
    {
        return response()->json(
            new MediaSearchResponse()
                ->setMedia(array_map(
                    fn (Media $media): MediaSummary => $this->converter->toOpenApiMediaSummary(
                        $media,
                        $outputData->usageCounts[$media->mediaId->value] ?? new MediaUsageCount(0, 0),
                    ),
                    $outputData->media,
                ))
                ->setMaxPage($outputData->maxPage),
            200,
        );
    }
}
