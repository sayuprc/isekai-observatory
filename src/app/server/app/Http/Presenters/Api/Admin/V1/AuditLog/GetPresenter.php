<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\AuditLog;

use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\AuditLogGetResponse;
use Support\UseCase\AuditLog\Get\GetOutputData;

class GetPresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    public function present(GetOutputData $outputData): JsonResponse
    {
        return response()->json(
            new AuditLogGetResponse()->setAuditLog($this->converter->toOpenApiAuditLog($outputData->auditLog)),
            200,
        );
    }
}
