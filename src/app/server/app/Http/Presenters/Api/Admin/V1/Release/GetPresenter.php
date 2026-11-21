<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Release;

use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\ReleaseGetResponse;
use Release\Application\Admin\UseCase\Get\GetOutputData;

class GetPresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    public function present(GetOutputData $outputData): JsonResponse
    {
        return response()->json(
            new ReleaseGetResponse()
                ->setRelease($this->converter->toOpenApiRelease($outputData->release))
                ->setReleaseGroupTitle($outputData->releaseGroup->title->value)
                ->setSongs(array_map($this->converter->toOpenApiReferencedSong(...), $outputData->songs)),
            200,
        );
    }
}
