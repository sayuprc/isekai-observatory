<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Media;

use Illuminate\Http\JsonResponse;
use Media\Application\Admin\UseCase\Get\GetOutputData;
use OpenAPI\Admin\Client\Model\MediaGetResponse;

class GetPresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    public function present(GetOutputData $outputData): JsonResponse
    {
        return response()->json(
            new MediaGetResponse()
                ->setMedia($this->converter->toOpenApiMedia($outputData->media))
                ->setSongs(array_map($this->converter->toOpenApiReferencedSong(...), $outputData->songs)),
            200,
        );
    }
}
