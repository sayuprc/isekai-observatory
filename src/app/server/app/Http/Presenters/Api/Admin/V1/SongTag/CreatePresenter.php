<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\SongTag;

use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\SongTagCreateResponse;
use Song\Application\Admin\UseCase\Tag\Create\CreateOutputData;

class CreatePresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    public function present(CreateOutputData $outputData): JsonResponse
    {
        return response()->json(
            new SongTagCreateResponse()->setTag($this->converter->toOpenApiSongTag($outputData->tag)),
            200,
        );
    }
}
