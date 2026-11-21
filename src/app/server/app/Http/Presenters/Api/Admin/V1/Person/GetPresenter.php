<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Person;

use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\PersonGetResponse;
use Person\Application\Admin\UseCase\Get\GetOutputData;

class GetPresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    public function present(GetOutputData $outputData): JsonResponse
    {
        return response()->json(
            new PersonGetResponse()->setPerson($this->converter->toOpenApiPerson($outputData->person)),
            200,
        );
    }
}
