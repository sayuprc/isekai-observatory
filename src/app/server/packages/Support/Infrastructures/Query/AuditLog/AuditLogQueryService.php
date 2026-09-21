<?php

declare(strict_types=1);

namespace Support\Infrastructures\Query\AuditLog;

use DateTimeImmutable;
use Emonkak\Orm\SelectBuilder;
use Override;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Infrastructures\Database\QueryFactory;
use Support\Infrastructures\Database\Row;
use Support\Infrastructures\Database\SqlHelper;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditTargetType;
use Support\UseCase\AuditLog\Query\AuditLogDetail;
use Support\UseCase\AuditLog\Query\AuditLogQueryServiceInterface;
use Support\UseCase\AuditLog\Query\AuditLogSearchCriteria;
use Support\UseCase\AuditLog\Query\AuditLogSummary;

readonly class AuditLogQueryService implements AuditLogQueryServiceInterface
{
    /** @var list<string> */
    private const array SUMMARY_COLUMNS = [
        'audit_logs.audit_log_id',
        'audit_logs.admin_user_id',
        'audit_logs.action',
        'audit_logs.target_type',
        'audit_logs.target_id',
        'audit_logs.created_at',
    ];

    public function __construct(
        private QueryFactory $queryFactory,
        private UuidConverterInterface $converter,
    ) {
    }

    #[Override]
    public function search(AuditLogSearchCriteria $criteria): array
    {
        $offset = ($criteria->page - 1) * $criteria->perPage->value;

        $rows = $this->queryFactory->fetchAll(
            $this->buildQuery($criteria)
                ->orderBy('audit_logs.created_at', 'desc')
                ->orderBy('audit_logs.audit_log_id', 'desc')
                ->limit($criteria->perPage->value)
                ->offset($offset),
        );

        return array_map($this->hydrateSummary(...), $rows);
    }

    #[Override]
    public function maxPage(AuditLogSearchCriteria $criteria): int
    {
        $count = Row::intValue($this->buildQuery($criteria)->aggregate($this->queryFactory->pdo(), 'COUNT(*)'));

        return (int)ceil($count / $criteria->perPage->value);
    }

    #[Override]
    public function find(string $auditLogId): ?AuditLogDetail
    {
        $rows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect([...self::SUMMARY_COLUMNS, 'audit_logs.snapshot'])
                ->select('admin_users.name', 'admin_user_name')
                ->from('audit_logs')
                ->outerJoin('admin_users', 'audit_logs.admin_user_id = admin_users.admin_user_id')
                ->where('audit_logs.audit_log_id', '=', $this->converter->toBin($auditLogId))
                ->limit(1),
        );

        $row = $rows[0] ?? null;

        if (is_null($row)) {
            return null;
        }

        /** @var array<string, mixed> $snapshot */
        $snapshot = json_decode(Row::string($row, 'snapshot'), true, flags: JSON_THROW_ON_ERROR);

        return new AuditLogDetail(
            $this->converter->toUuid(Row::string($row, 'audit_log_id')),
            $this->converter->toUuid(Row::string($row, 'admin_user_id')),
            Row::nullableString($row, 'admin_user_name') ?? '',
            AuditAction::from(Row::string($row, 'action')),
            AuditTargetType::from(Row::string($row, 'target_type')),
            $this->converter->toUuid(Row::string($row, 'target_id')),
            $snapshot,
            new DateTimeImmutable(Row::string($row, 'created_at')),
        );
    }

    private function buildQuery(AuditLogSearchCriteria $criteria): SelectBuilder
    {
        $query = $this->queryFactory->select()
            ->withSelect(self::SUMMARY_COLUMNS)
            ->select('admin_users.name', 'admin_user_name')
            ->from('audit_logs')
            ->outerJoin('admin_users', 'audit_logs.admin_user_id = admin_users.admin_user_id');

        if ($criteria->from->isPresent()) {
            $query = $query->where('audit_logs.created_at', '>=', $criteria->from->get()->format('Y-m-d H:i:s'));
        }

        if ($criteria->to->isPresent()) {
            $query = $query->where('audit_logs.created_at', '<=', $criteria->to->get()->format('Y-m-d H:i:s'));
        }

        if ($criteria->action->isPresent()) {
            $query = $query->where('audit_logs.action', '=', $criteria->action->get()->value);
        }

        if ($criteria->targetType->isPresent()) {
            $query = $query->where('audit_logs.target_type', '=', $criteria->targetType->get()->value);
        }

        if ($criteria->targetId->isPresent()) {
            $query = $query->where('audit_logs.target_id', '=', $this->converter->toBin($criteria->targetId->get()));
        }

        if ($criteria->adminUserName->isPresent()) {
            $query = $query->where(
                'admin_users.name',
                'LIKE',
                '%' . SqlHelper::escapeLike($criteria->adminUserName->get()) . '%',
            );
        }

        return $query;
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrateSummary(array $row): AuditLogSummary
    {
        return new AuditLogSummary(
            $this->converter->toUuid(Row::string($row, 'audit_log_id')),
            $this->converter->toUuid(Row::string($row, 'admin_user_id')),
            Row::nullableString($row, 'admin_user_name') ?? '',
            AuditAction::from(Row::string($row, 'action')),
            AuditTargetType::from(Row::string($row, 'target_type')),
            $this->converter->toUuid(Row::string($row, 'target_id')),
            new DateTimeImmutable(Row::string($row, 'created_at')),
        );
    }
}
