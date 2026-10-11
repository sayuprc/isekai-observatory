<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\ReleaseGroup;

use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\ReleaseGroupUpdateResponse;
use Release\Application\Admin\UseCase\Group\Update\UpdateOutputData;

class UpdatePresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    public function present(UpdateOutputData $outputData): JsonResponse
    {
        return response()->json(
            new ReleaseGroupUpdateResponse()->setReleaseGroup(
                $this->converter->toOpenApiReleaseGroup($outputData->releaseGroup),
            ),
            200,
        );
    }
}
