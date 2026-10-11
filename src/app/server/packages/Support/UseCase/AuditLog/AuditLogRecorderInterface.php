<?php

declare(strict_types=1);

namespace Support\UseCase\AuditLog;

use AdminUser\Domain\Models\AdminUserId;
use Support\Domain\ValueObjects\String\UuidValueObject;

interface AuditLogRecorderInterface
{
    /**
     * $snapshot は原則として Entity の `toArray()` をそのまま渡す
     * パスワード等の機密情報を含む集約のときだけ、機密値を除外した配列を手組みで渡すこと
     *
     * @param array<string, mixed> $snapshot
     */
    public function record(
        AuditAction $action,
        AuditTargetType $targetType,
        UuidValueObject $targetId,
        array $snapshot,
        ?AdminUserId $actorId = null,
    ): void;
}
