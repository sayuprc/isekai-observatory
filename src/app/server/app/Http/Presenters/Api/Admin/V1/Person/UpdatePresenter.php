<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Person;

use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\PersonUpdateResponse;
use Person\Application\Admin\UseCase\Update\UpdateOutputData;

class UpdatePresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    public function present(UpdateOutputData $outputData): JsonResponse
    {
        return response()->json(
            new PersonUpdateResponse()->setPerson($this->converter->toOpenApiPerson($outputData->person)),
            200,
        );
    }
}
