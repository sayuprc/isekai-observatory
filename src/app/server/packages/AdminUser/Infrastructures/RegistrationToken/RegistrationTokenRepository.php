<?php

declare(strict_types=1);

namespace AdminUser\Infrastructures\RegistrationToken;

use AdminUser\Domain\Models\Email;
use AdminUser\Domain\Models\RegistrationToken\ConsumptionStatus;
use AdminUser\Domain\Models\RegistrationToken\RegistrationToken;
use AdminUser\Domain\Models\RegistrationToken\RegistrationTokenRepositoryInterface;
use DateTimeImmutable;
use Override;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Infrastructures\Database\QueryFactory;
use Support\Infrastructures\Database\Row;

readonly class RegistrationTokenRepository implements RegistrationTokenRepositoryInterface
{
    private const string TABLE = 'admin_user_registration_tokens';

    private const string PERMISSION_TABLE = 'admin_user_registration_token_permissions';

    /** @var list<string> */
    private const array COLUMNS = ['admin_user_registration_token_id', 'token', 'email', 'role', 'expired_at', 'status'];

    public function __construct(
        private QueryFactory $queryFactory,
        private UuidConverterInterface $converter,
    ) {
    }

    #[Override]
    public function save(RegistrationToken $token): RegistrationToken
    {
        $data = $token->toArray();
        $id = $this->converter->toBin($data['admin_user_registration_token_id']);
        $now = now()->toDateTimeString();

        $this->queryFactory->insert()
            ->into(self::TABLE, ['admin_user_registration_token_id', 'token', 'email', 'role', 'expired_at', 'status', 'created_at', 'updated_at'])
            ->values([
                $id,
                $data['token'],
                $data['email'],
                $data['role'],
                $data['expired_at'],
                $data['status'],
                $now,
                $now,
            ])
            ->build()
            ->append(
                'ON DUPLICATE KEY UPDATE '
                . '`token` = VALUES(`token`), '
                . '`email` = VALUES(`email`), '
                . '`role` = VALUES(`role`), '
                . '`expired_at` = VALUES(`expired_at`), '
                . '`status` = VALUES(`status`), '
                . '`updated_at` = VALUES(`updated_at`)',
            )
            ->execute($this->queryFactory->pdo());

        if ($data['permissions'] !== [] && ! $this->hasPermissions($id)) {
            $rows = array_map(static fn (string $permission): array => [$id, $permission], $data['permissions']);

            $this->queryFactory->insert()
                ->into(self::PERMISSION_TABLE, ['admin_user_registration_token_id', 'permission'])
                ->values(...$rows)
                ->execute($this->queryFactory->pdo());
        }

        return $token;
    }

    #[Override]
    public function findByEmailForUpdate(Email $email): ?RegistrationToken
    {
        $rows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(self::COLUMNS)
                ->from(self::TABLE)
                ->where('email', '=', $email->value)
                ->orderBy('created_at', 'desc')
                ->limit(1)
                ->forUpdate(),
        );

        $row = $rows[0] ?? null;

        if (is_null($row)) {
            return null;
        }

        $permissionsByToken = $this->loadPermissions([Row::string($row, 'admin_user_registration_token_id')]);

        return $this->hydrate($row, $permissionsByToken[Row::string($row, 'admin_user_registration_token_id')] ?? []);
    }

    /**
     * @return list<RegistrationToken>
     */
    #[Override]
    public function findUnusedByEmailForUpdate(Email $email): array
    {
        $rows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(self::COLUMNS)
                ->from(self::TABLE)
                ->where('email', '=', $email->value)
                ->where('status', '=', ConsumptionStatus::Unused->value)
                ->orderBy('created_at', 'desc')
                ->forUpdate(),
        );

        $permissionsByToken = $this->loadPermissions(
            array_map(static fn (array $row): string => Row::string($row, 'admin_user_registration_token_id'), $rows),
        );

        return array_map(
            fn (array $row): RegistrationToken => $this->hydrate(
                $row,
                $permissionsByToken[Row::string($row, 'admin_user_registration_token_id')] ?? [],
            ),
            $rows,
        );
    }

    private function hasPermissions(string $binTokenId): bool
    {
        $count = Row::intValue(
            $this->queryFactory->select()
                ->from(self::PERMISSION_TABLE)
                ->where('admin_user_registration_token_id', '=', $binTokenId)
                ->aggregate($this->queryFactory->pdo(), 'COUNT(*)'),
        );

        return $count > 0;
    }

    /**
     * @param list<string> $binTokenIds
     *
     * @return array<string, list<string>>
     */
    private function loadPermissions(array $binTokenIds): array
    {
        if ($binTokenIds === []) {
            return [];
        }

        $rows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(['admin_user_registration_token_id', 'permission'])
                ->from(self::PERMISSION_TABLE)
                ->where('admin_user_registration_token_id', 'IN', $binTokenIds),
        );

        $grouped = [];

        foreach ($rows as $row) {
            $grouped[Row::string($row, 'admin_user_registration_token_id')][] = Row::string($row, 'permission');
        }

        return $grouped;
    }

    /**
     * @param array<string, mixed> $row
     * @param list<string>         $permissions
     */
    private function hydrate(array $row, array $permissions): RegistrationToken
    {
        return RegistrationToken::reconstruct(
            $this->converter->toUuid(Row::string($row, 'admin_user_registration_token_id')),
            Row::string($row, 'token'),
            Row::string($row, 'email'),
            Row::int($row, 'role'),
            $permissions,
            new DateTimeImmutable(Row::string($row, 'expired_at')),
            Row::int($row, 'status'),
        );
    }
}
