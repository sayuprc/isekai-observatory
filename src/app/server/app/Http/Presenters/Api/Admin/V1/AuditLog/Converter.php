<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\AuditLog;

use DateTime;
use DateTimeInterface;
use OpenAPI\Admin\Client\Model\AuditAction as OpenApiAuditAction;
use OpenAPI\Admin\Client\Model\AuditLog as OpenApiAuditLog;
use OpenAPI\Admin\Client\Model\AuditLogSummary as OpenApiAuditLogSummary;
use OpenAPI\Admin\Client\Model\AuditTargetType as OpenApiAuditTargetType;
use Support\UseCase\AuditLog\Query\AuditLogDetail;
use Support\UseCase\AuditLog\Query\AuditLogSummary;

readonly class Converter
{
    public function toOpenApiSummary(AuditLogSummary $summary): OpenApiAuditLogSummary
    {
        return new OpenApiAuditLogSummary()
            ->setAuditLogId($summary->auditLogId)
            ->setAdminUserId($summary->adminUserId)
            ->setAdminUserName($summary->adminUserName)
            ->setAction(OpenApiAuditAction::from($summary->action->value))
            ->setTargetType(OpenApiAuditTargetType::from($summary->targetType->value))
            ->setTargetId($summary->targetId)
            ->setCreatedAt($this->toDateTime($summary->createdAt));
    }

    public function toOpenApiAuditLog(AuditLogDetail $detail): OpenApiAuditLog
    {
        return new OpenApiAuditLog()
            ->setAuditLogId($detail->auditLogId)
            ->setAdminUserId($detail->adminUserId)
            ->setAdminUserName($detail->adminUserName)
            ->setAction(OpenApiAuditAction::from($detail->action->value))
            ->setTargetType(OpenApiAuditTargetType::from($detail->targetType->value))
            ->setTargetId($detail->targetId)
            ->setCreatedAt($this->toDateTime($detail->createdAt))
            ->setSnapshot((object)$detail->snapshot);
    }

    private function toDateTime(DateTimeInterface $value): DateTime
    {
        return DateTime::createFromInterface($value);
    }
}
