<?php

declare(strict_types=1);

namespace Tests\Support\Concerns;

use Illuminate\Support\Facades\DB;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditTargetType;

trait AssertsAuditLog
{
    /**
     * @return array<string, mixed>
     */
    protected function findAuditLog(
        AuditAction $action,
        AuditTargetType $targetType,
        string $targetId,
    ): array {
        $converter = $this->app->make(UuidConverterInterface::class);

        $found = DB::table('audit_logs')
            ->where('action', $action->value)
            ->where('target_type', $targetType->value)
            ->where('target_id', $converter->toBin($targetId))
            ->first();

        $this->assertNotNull($found, sprintf(
            '監査ログが見つかりません: action=%s target=%s:%s',
            $action->value,
            $targetType->value,
            $targetId,
        ));

        return [
            'admin_user_id' => $converter->toUuid((string)$found->admin_user_id),
            'action' => $found->action,
            'target_type' => $found->target_type,
            'target_id' => $converter->toUuid((string)$found->target_id),
            'snapshot' => json_decode((string)$found->snapshot, true),
        ];
    }

    protected function assertAuditLogCount(int $expected): void
    {
        $this->assertSame($expected, DB::table('audit_logs')->count());
    }
}
