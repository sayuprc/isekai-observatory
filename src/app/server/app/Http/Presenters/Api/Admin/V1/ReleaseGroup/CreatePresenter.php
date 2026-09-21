<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\ReleaseGroup;

use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\ReleaseGroupCreateResponse;
use Release\Application\Admin\UseCase\Group\Create\CreateOutputData;

class CreatePresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    public function present(CreateOutputData $outputData): JsonResponse
    {
        return response()->json(
            new ReleaseGroupCreateResponse()->setReleaseGroup(
                $this->converter->toOpenApiReleaseGroup($outputData->releaseGroup),
            ),
            200,
        );
    }
}
