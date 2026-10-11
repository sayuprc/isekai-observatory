<?php

declare(strict_types=1);

namespace Support\UseCase\AuditLog\Query;

use DateTimeImmutable;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditTargetType;

readonly class AuditLogSummary
{
    public function __construct(
        public string $auditLogId,
        public string $adminUserId,
        public string $adminUserName,
        public AuditAction $action,
        public AuditTargetType $targetType,
        public string $targetId,
        public DateTimeImmutable $createdAt,
    ) {
    }
}
