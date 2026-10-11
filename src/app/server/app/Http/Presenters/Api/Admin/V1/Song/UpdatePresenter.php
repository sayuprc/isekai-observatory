<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Song;

use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\SongUpdateResponse;
use Song\Application\Admin\UseCase\Update\UpdateOutputData;

class UpdatePresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    public function present(UpdateOutputData $outputData): JsonResponse
    {
        return response()->json(
            new SongUpdateResponse()->setSong($this->converter->toOpenApiSong($outputData->song)),
            200,
        );
    }
}
