<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\ReleaseGroup;

use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\ReleaseGroupGetResponse;
use Release\Application\Admin\UseCase\Group\Get\GetOutputData;

class GetPresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    public function present(GetOutputData $outputData): JsonResponse
    {
        return response()->json(
            new ReleaseGroupGetResponse()
                ->setReleaseGroup($this->converter->toOpenApiReleaseGroup($outputData->releaseGroup))
                ->setReleases(array_map($this->converter->toOpenApiReferencedRelease(...), $outputData->releases)),
            200,
        );
    }
}
