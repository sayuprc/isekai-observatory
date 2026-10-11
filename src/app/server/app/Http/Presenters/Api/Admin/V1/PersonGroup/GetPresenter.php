<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\PersonGroup;

use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\PersonGroupGetResponse;
use Person\Application\Admin\UseCase\Group\Get\GetOutputData;

class GetPresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    public function present(GetOutputData $outputData): JsonResponse
    {
        return response()->json(
            new PersonGroupGetResponse()->setPersonGroup($this->converter->toOpenApiPersonGroup($outputData->personGroup)),
            200,
        );
    }
}
