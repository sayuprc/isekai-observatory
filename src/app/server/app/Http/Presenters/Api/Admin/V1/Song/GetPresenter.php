<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Song;

use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\SongGetResponse;
use Song\Application\Admin\UseCase\Get\GetOutputData;

class GetPresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    public function present(GetOutputData $outputData): JsonResponse
    {
        return response()->json(
            new SongGetResponse()->setSong($this->converter->toOpenApiSong($outputData->song)),
            200,
        );
    }
}
