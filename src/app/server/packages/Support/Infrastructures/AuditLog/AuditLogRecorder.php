<?php

declare(strict_types=1);

namespace Support\Infrastructures\AuditLog;

use AdminUser\Domain\Models\AdminUserId;
use Auth\Domain\Models\AuthContext;
use LogicException;
use Override;
use Support\Contracts\ClockInterface;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Contracts\Uuid\UuidGeneratorInterface;
use Support\Domain\ValueObjects\String\UuidValueObject;
use Support\Infrastructures\Database\QueryFactory;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditLogRecorderInterface;
use Support\UseCase\AuditLog\AuditTargetType;

readonly class AuditLogRecorder implements AuditLogRecorderInterface
{
    public function __construct(
        private ClockInterface $clock,
        private UuidGeneratorInterface $uuidGenerator,
        private UuidConverterInterface $uuidConverter,
        private AuthContext $authContext,
        private QueryFactory $queryFactory,
    ) {
    }

    /**
     * @param array<string, mixed> $snapshot
     */
    #[Override]
    public function record(
        AuditAction $action,
        AuditTargetType $targetType,
        UuidValueObject $targetId,
        array $snapshot,
        ?AdminUserId $actorId = null,
    ): void {
        $resolvedActor = $actorId ?? $this->authContext->get()?->adminUserId;

        if (is_null($resolvedActor)) {
            throw new LogicException('audit log の actor が解決できません');
        }

        $this->queryFactory->insert()
            ->into('audit_logs', ['audit_log_id', 'admin_user_id', 'action', 'target_type', 'target_id', 'snapshot', 'created_at'])
            ->values([
                $this->uuidConverter->toBin($this->uuidGenerator->generate()),
                $this->uuidConverter->toBin($resolvedActor->value),
                $action->value,
                $targetType->value,
                $this->uuidConverter->toBin($targetId->value),
                (string)json_encode($snapshot, JSON_THROW_ON_ERROR),
                $this->clock->now()->format('Y-m-d H:i:s'),
            ])
            ->execute($this->queryFactory->pdo());
    }
}
