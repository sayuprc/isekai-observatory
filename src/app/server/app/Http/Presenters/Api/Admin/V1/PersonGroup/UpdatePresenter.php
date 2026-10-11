<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\PersonGroup;

use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\PersonGroupUpdateResponse;
use Person\Application\Admin\UseCase\Group\Update\UpdateOutputData;

class UpdatePresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    public function present(UpdateOutputData $outputData): JsonResponse
    {
        return response()->json(
            new PersonGroupUpdateResponse()->setPersonGroup($this->converter->toOpenApiPersonGroup($outputData->personGroup)),
            200,
        );
    }
}
