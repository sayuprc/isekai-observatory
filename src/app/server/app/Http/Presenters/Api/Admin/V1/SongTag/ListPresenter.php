<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\SongTag;

use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\SongTagListResponse;
use Song\Application\Admin\UseCase\Tag\List\ListOutputData;

class ListPresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    public function present(ListOutputData $outputData): JsonResponse
    {
        return response()->json(
            new SongTagListResponse()->setTags(
                array_map(
                    $this->converter->toOpenApiSongTag(...),
                    $outputData->tags,
                ),
            ),
            200,
        );
    }
}
