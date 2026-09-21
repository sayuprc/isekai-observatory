<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\SongTag;

use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\SongTagUpdateResponse;
use Song\Application\Admin\UseCase\Tag\Update\UpdateOutputData;

class UpdatePresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    public function present(UpdateOutputData $outputData): JsonResponse
    {
        return response()->json(
            new SongTagUpdateResponse()->setTag($this->converter->toOpenApiSongTag($outputData->tag)),
            200,
        );
    }
}
