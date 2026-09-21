<?php

declare(strict_types=1);

namespace Support\UseCase\AuditLog\Query;

use DateTimeImmutable;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditTargetType;

readonly class AuditLogDetail
{
    /**
     * @param array<string, mixed> $snapshot
     */
    public function __construct(
        public string $auditLogId,
        public string $adminUserId,
        public string $adminUserName,
        public AuditAction $action,
        public AuditTargetType $targetType,
        public string $targetId,
        public array $snapshot,
        public DateTimeImmutable $createdAt,
    ) {
    }
}
