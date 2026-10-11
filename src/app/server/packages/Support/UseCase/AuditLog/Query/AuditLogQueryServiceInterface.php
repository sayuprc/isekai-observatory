<?php

declare(strict_types=1);

namespace Support\UseCase\AuditLog\Query;

interface AuditLogQueryServiceInterface
{
    /**
     * @return array<AuditLogSummary>
     */
    public function search(AuditLogSearchCriteria $criteria): array;

    public function maxPage(AuditLogSearchCriteria $criteria): int;

    public function find(string $auditLogId): ?AuditLogDetail;
}
