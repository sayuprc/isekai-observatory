<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\AuditLog;

use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\AuditLogSearchResponse;
use Support\UseCase\AuditLog\Search\SearchOutputData;

class SearchPresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    public function present(SearchOutputData $outputData): JsonResponse
    {
        return response()->json(
            new AuditLogSearchResponse()
                ->setAuditLogs(array_map($this->converter->toOpenApiSummary(...), $outputData->auditLogs))
                ->setMaxPage($outputData->maxPage),
            200,
        );
    }
}
