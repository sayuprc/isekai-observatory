<?php

declare(strict_types=1);

namespace AdminUser\Infrastructures;

use AdminUser\Domain\Exceptions\DuplicateAdminUserEmailException;
use AdminUser\Domain\Models\AdminUser;
use AdminUser\Domain\Models\AdminUserId;
use AdminUser\Domain\Models\AdminUserRepositoryInterface;
use AdminUser\Domain\Models\Email;
use DateTimeImmutable;
use Emonkak\Orm\SelectBuilder;
use Override;
use PDOException;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Infrastructures\Database\QueryFactory;
use Support\Infrastructures\Database\Row;

readonly class AdminUserRepository implements AdminUserRepositoryInterface
{
    private const string TABLE = 'admin_users';

    private const string PERMISSION_TABLE = 'admin_user_permissions';

    /** @var list<string> */
    private const array COLUMNS = ['admin_user_id', 'name', 'email', 'role', 'created_at'];

    public function __construct(
        private QueryFactory $queryFactory,
        private UuidConverterInterface $converter,
    ) {
    }

    #[Override]
    public function all(): array
    {
        $rows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(self::COLUMNS)
                ->from(self::TABLE)
                ->orderBy('created_at'),
        );

        $permissionsByUser = $this->loadPermissions(
            array_map(static fn (array $row): string => Row::string($row, 'admin_user_id'), $rows),
        );

        return array_map(
            fn (array $row): AdminUser => $this->hydrate(
                $row,
                $permissionsByUser[Row::string($row, 'admin_user_id')] ?? [],
            ),
            $rows,
        );
    }

    #[Override]
    public function find(AdminUserId $adminUserId): ?AdminUser
    {
        return $this->fetchOne(
            $this->baseQuery()->where('admin_user_id', '=', $this->converter->toBin($adminUserId->value)),
        );
    }

    #[Override]
    public function findByIdForUpdate(AdminUserId $adminUserId): ?AdminUser
    {
        return $this->fetchOne(
            $this->baseQuery()
                ->where('admin_user_id', '=', $this->converter->toBin($adminUserId->value))
                ->forUpdate(),
        );
    }

    #[Override]
    public function findByEmail(Email $email): ?AdminUser
    {
        return $this->fetchOne(
            $this->baseQuery()->where('email', '=', $email->value),
        );
    }

    #[Override]
    public function findByEmailForUpdate(Email $email): ?AdminUser
    {
        return $this->fetchOne(
            $this->baseQuery()->where('email', '=', $email->value)->forUpdate(),
        );
    }

    #[Override]
    public function register(AdminUser $adminUser): AdminUser
    {
        $data = $adminUser->toArray();
        $id = $this->converter->toBin($data['admin_user_id']);

        try {
            $this->queryFactory->insert()
                ->into(self::TABLE, ['admin_user_id', 'name', 'email', 'role', 'created_at', 'updated_at'])
                ->values([
                    $id,
                    $data['name'],
                    $data['email'],
                    $data['role'],
                    $data['created_at'],
                    now()->toDateTimeString(),
                ])
                ->execute($this->queryFactory->pdo());
        } catch (PDOException $exception) {
            if ($this->isDuplicateEmail($exception)) {
                throw new DuplicateAdminUserEmailException(
                    sprintf('すでに使われているメールアドレスです "%s"', $data['email']),
                    previous: $exception,
                );
            }

            throw $exception;
        }

        if ($data['permissions'] !== []) {
            $rows = array_map(static fn (string $permission): array => [$id, $permission], $data['permissions']);

            $this->queryFactory->insert()
                ->into(self::PERMISSION_TABLE, ['admin_user_id', 'permission'])
                ->values(...$rows)
                ->execute($this->queryFactory->pdo());
        }

        return $adminUser;
    }

    private function baseQuery(): SelectBuilder
    {
        return $this->queryFactory->select()->withSelect(self::COLUMNS)->from(self::TABLE);
    }

    private function fetchOne(SelectBuilder $query): ?AdminUser
    {
        $rows = $this->queryFactory->fetchAll($query->limit(1));

        $row = $rows[0] ?? null;

        if (is_null($row)) {
            return null;
        }

        $permissionsByUser = $this->loadPermissions([Row::string($row, 'admin_user_id')]);

        return $this->hydrate($row, $permissionsByUser[Row::string($row, 'admin_user_id')] ?? []);
    }

    /**
     * @param list<string> $binAdminUserIds
     *
     * @return array<string, list<string>>
     */
    private function loadPermissions(array $binAdminUserIds): array
    {
        if ($binAdminUserIds === []) {
            return [];
        }

        $rows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(['admin_user_id', 'permission'])
                ->from(self::PERMISSION_TABLE)
                ->where('admin_user_id', 'IN', $binAdminUserIds),
        );

        $grouped = [];

        foreach ($rows as $row) {
            $grouped[Row::string($row, 'admin_user_id')][] = Row::string($row, 'permission');
        }

        return $grouped;
    }

    private function isDuplicateEmail(PDOException $exception): bool
    {
        return ($exception->errorInfo[0] ?? null) === '23000'
            && str_contains($exception->getMessage(), 'admin_users_email_unique');
    }

    /**
     * @param array<string, mixed> $row
     * @param list<string>         $permissions
     */
    private function hydrate(array $row, array $permissions): AdminUser
    {
        return AdminUser::reconstruct(
            $this->converter->toUuid(Row::string($row, 'admin_user_id')),
            Row::string($row, 'name'),
            Row::string($row, 'email'),
            new DateTimeImmutable(Row::string($row, 'created_at')),
            Row::int($row, 'role'),
            $permissions,
        );
    }
}
