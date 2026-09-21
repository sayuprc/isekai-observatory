<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\AuditLog;

use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditTargetType;

trait SeedsAuditLog
{
    /**
     * @param array<string, mixed> $snapshot
     */
    private function insertAuditLog(
        string $auditLogId,
        string $actorId,
        AuditAction $action,
        AuditTargetType $targetType,
        string $targetId,
        array $snapshot,
        DateTimeImmutable $createdAt,
    ): void {
        $converter = $this->app->make(UuidConverterInterface::class);

        DB::table('audit_logs')->insert([
            'audit_log_id' => $converter->toBin($auditLogId),
            'admin_user_id' => $converter->toBin($actorId),
            'action' => $action->value,
            'target_type' => $targetType->value,
            'target_id' => $converter->toBin($targetId),
            'snapshot' => json_encode($snapshot),
            'created_at' => $createdAt->format('Y-m-d H:i:s'),
        ]);
    }
}
