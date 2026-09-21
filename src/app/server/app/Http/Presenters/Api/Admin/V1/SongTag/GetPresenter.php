<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\SongTag;

use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\SongTagGetResponse;
use Song\Application\Admin\UseCase\Tag\Get\GetOutputData;

class GetPresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    public function present(GetOutputData $outputData): JsonResponse
    {
        return response()->json(
            new SongTagGetResponse()->setTag($this->converter->toOpenApiSongTag($outputData->tag)),
            200,
        );
    }
}
