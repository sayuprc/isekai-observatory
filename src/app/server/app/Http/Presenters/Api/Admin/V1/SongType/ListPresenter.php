<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\SongType;

use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\SongTypeListResponse;
use Song\Application\Admin\UseCase\Type\ListOutputData;

class ListPresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    public function present(ListOutputData $outputData): JsonResponse
    {
        return response()->json(
            new SongTypeListResponse()->setTypes(array_map($this->converter->toOpenApiSongType(...), $outputData->types)),
            200,
        );
    }
}
