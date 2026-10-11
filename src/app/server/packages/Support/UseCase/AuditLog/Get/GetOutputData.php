<?php

declare(strict_types=1);

namespace Support\UseCase\AuditLog\Get;

use Support\UseCase\AuditLog\Query\AuditLogDetail;

readonly class GetOutputData
{
    public function __construct(public AuditLogDetail $auditLog)
    {
    }
}
