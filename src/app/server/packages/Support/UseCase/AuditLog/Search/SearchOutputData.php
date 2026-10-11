<?php

declare(strict_types=1);

namespace Support\UseCase\AuditLog\Search;

use Support\UseCase\AuditLog\Query\AuditLogSummary;

readonly class SearchOutputData
{
    /**
     * @param array<AuditLogSummary> $auditLogs
     */
    public function __construct(
        public array $auditLogs,
        public int $maxPage,
    ) {
    }
}
