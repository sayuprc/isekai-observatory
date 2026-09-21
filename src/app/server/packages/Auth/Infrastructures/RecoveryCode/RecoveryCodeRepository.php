<?php

declare(strict_types=1);

namespace Auth\Infrastructures\RecoveryCode;

use AdminUser\Domain\Models\AdminUserId;
use Auth\Domain\Models\RecoveryCode\ConsumptionStatus;
use Auth\Domain\Models\RecoveryCode\RecoveryCode;
use Auth\Domain\Models\RecoveryCode\RecoveryCodeId;
use Auth\Domain\Models\RecoveryCode\RecoveryCodeRepositoryInterface;
use DateTimeImmutable;
use Override;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Infrastructures\Database\QueryFactory;
use Support\Infrastructures\Database\Row;

readonly class RecoveryCodeRepository implements RecoveryCodeRepositoryInterface
{
    private const string TABLE = 'admin_user_recovery_codes';

    /** @var list<string> */
    private const array COLUMNS = ['admin_user_recovery_code_id', 'admin_user_id', 'code', 'status', 'used_at'];

    public function __construct(
        private QueryFactory $queryFactory,
        private UuidConverterInterface $converter,
    ) {
    }

    /**
     * @param list<RecoveryCode> $codes
     */
    #[Override]
    public function saveMany(array $codes): void
    {
        if ($codes === []) {
            return;
        }

        $now = now()->toDateTimeString();

        $rows = array_map(function (RecoveryCode $code) use ($now): array {
            $data = $code->toArray();

            return [
                $this->converter->toBin($data['admin_user_recovery_code_id']),
                $this->converter->toBin($data['admin_user_id']),
                $data['code'],
                $data['status'],
                $data['used_at'],
                $now,
                $now,
            ];
        }, $codes);

        $this->queryFactory->insert()
            ->into(self::TABLE, ['admin_user_recovery_code_id', 'admin_user_id', 'code', 'status', 'used_at', 'created_at', 'updated_at'])
            ->values(...$rows)
            ->execute($this->queryFactory->pdo());
    }

    /**
     * @return list<RecoveryCode>
     */
    #[Override]
    public function findUnusedByAdminUserIdForUpdate(AdminUserId $adminUserId): array
    {
        $rows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(self::COLUMNS)
                ->from(self::TABLE)
                ->where('admin_user_id', '=', $this->converter->toBin($adminUserId->value))
                ->where('status', '=', ConsumptionStatus::Unused->value)
                ->orderBy('created_at', 'desc')
                ->forUpdate(),
        );

        return array_map($this->hydrate(...), $rows);
    }

    #[Override]
    public function findUnusedByIdForUpdate(RecoveryCodeId $recoveryCodeId, AdminUserId $adminUserId): ?RecoveryCode
    {
        $rows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(self::COLUMNS)
                ->from(self::TABLE)
                ->where('admin_user_recovery_code_id', '=', $this->converter->toBin($recoveryCodeId->value))
                ->where('admin_user_id', '=', $this->converter->toBin($adminUserId->value))
                ->where('status', '=', ConsumptionStatus::Unused->value)
                ->limit(1)
                ->forUpdate(),
        );

        $row = $rows[0] ?? null;

        return is_null($row) ? null : $this->hydrate($row);
    }

    #[Override]
    public function save(RecoveryCode $code): RecoveryCode
    {
        $data = $code->toArray();
        $now = now()->toDateTimeString();

        $this->queryFactory->insert()
            ->into(self::TABLE, ['admin_user_recovery_code_id', 'admin_user_id', 'code', 'status', 'used_at', 'created_at', 'updated_at'])
            ->values([
                $this->converter->toBin($data['admin_user_recovery_code_id']),
                $this->converter->toBin($data['admin_user_id']),
                $data['code'],
                $data['status'],
                $data['used_at'],
                $now,
                $now,
            ])
            ->build()
            ->append('ON DUPLICATE KEY UPDATE `status` = VALUES(`status`), `used_at` = VALUES(`used_at`), `updated_at` = VALUES(`updated_at`)')
            ->execute($this->queryFactory->pdo());

        return $code;
    }

    #[Override]
    public function deleteByAdminUserId(AdminUserId $adminUserId): void
    {
        $this->queryFactory->delete()
            ->from(self::TABLE)
            ->where('admin_user_id', '=', $this->converter->toBin($adminUserId->value))
            ->execute($this->queryFactory->pdo());
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrate(array $row): RecoveryCode
    {
        $usedAt = Row::nullableString($row, 'used_at');

        return RecoveryCode::reconstruct(
            $this->converter->toUuid(Row::string($row, 'admin_user_recovery_code_id')),
            $this->converter->toUuid(Row::string($row, 'admin_user_id')),
            Row::string($row, 'code'),
            Row::int($row, 'status'),
            is_null($usedAt) ? null : new DateTimeImmutable($usedAt),
        );
    }
}
