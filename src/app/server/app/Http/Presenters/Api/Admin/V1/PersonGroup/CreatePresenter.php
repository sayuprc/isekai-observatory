<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\PersonGroup;

use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\PersonGroupCreateResponse;
use Person\Application\Admin\UseCase\Group\Create\CreateOutputData;

class CreatePresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    public function present(CreateOutputData $outputData): JsonResponse
    {
        return response()->json(
            new PersonGroupCreateResponse()->setPersonGroup($this->converter->toOpenApiPersonGroup($outputData->personGroup)),
            200,
        );
    }
}
