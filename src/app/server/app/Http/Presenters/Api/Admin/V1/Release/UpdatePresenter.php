<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Release;

use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\ReleaseUpdateResponse;
use Release\Application\Admin\UseCase\Update\UpdateOutputData;

class UpdatePresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    public function present(UpdateOutputData $outputData): JsonResponse
    {
        return response()->json(
            new ReleaseUpdateResponse()->setRelease(
                $this->converter->toOpenApiRelease($outputData->release),
            ),
            200,
        );
    }
}
