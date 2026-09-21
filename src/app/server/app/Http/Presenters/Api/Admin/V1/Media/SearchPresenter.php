<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Media;

use Illuminate\Http\JsonResponse;
use Media\Application\Admin\UseCase\Search\SearchOutputData;
use OpenAPI\Admin\Client\Model\MediaSearchResponse;

class SearchPresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    public function present(SearchOutputData $outputData): JsonResponse
    {
        return response()->json(
            new MediaSearchResponse()
                ->setMedia(array_map($this->converter->toOpenApiMedia(...), $outputData->media))
                ->setMaxPage($outputData->maxPage),
            200,
        );
    }
}
