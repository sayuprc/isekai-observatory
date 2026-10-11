<?php

declare(strict_types=1);

namespace Support\UseCase\AuditLog\Get;

readonly class GetInputData
{
    public function __construct(public string $auditLogId)
    {
    }
}
