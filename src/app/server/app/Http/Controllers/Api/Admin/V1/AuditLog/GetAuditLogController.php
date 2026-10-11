<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\AuditLog;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\AuditLog\GetPresenter;
use Illuminate\Http\JsonResponse;
use Support\UseCase\AuditLog\Get\GetInputData;
use Support\UseCase\AuditLog\Get\GetUseCase;

class GetAuditLogController extends Controller
{
    public function __construct(
        private readonly GetUseCase $useCase,
        private readonly GetPresenter $presenter,
    ) {
    }

    public function handle(string $auditLogId): JsonResponse
    {
        return $this->presenter->present($this->useCase->handle(new GetInputData($auditLogId)));
    }
}
