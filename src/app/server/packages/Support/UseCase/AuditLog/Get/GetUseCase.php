<?php

declare(strict_types=1);

namespace Support\UseCase\AuditLog\Get;

use AdminUser\Domain\Models\Permission;
use Support\UseCase\AuditLog\Query\AuditLogQueryServiceInterface;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Support\UseCase\Exceptions\ResourceNotFoundException;

readonly class GetUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private AuditLogQueryServiceInterface $query,
    ) {
    }

    public function handle(GetInputData $inputData): GetOutputData
    {
        $this->authorizer->authorize(Permission::ReadAuditLog);

        $found = $this->query->find($inputData->auditLogId);

        if (is_null($found)) {
            throw new ResourceNotFoundException('監査ログ', $inputData->auditLogId);
        }

        return new GetOutputData($found);
    }
}
