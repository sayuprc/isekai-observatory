<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Release;

use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\ReleaseCreateResponse;
use Release\Application\Admin\UseCase\Create\CreateOutputData;

class CreatePresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    public function present(CreateOutputData $outputData): JsonResponse
    {
        return response()->json(
            new ReleaseCreateResponse()->setRelease(
                $this->converter->toOpenApiRelease($outputData->release),
            ),
            200,
        );
    }
}
