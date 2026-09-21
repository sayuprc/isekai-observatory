<?php

declare(strict_types=1);

namespace Auth\Infrastructures;

use Auth\Domain\Models\AdminUserPasskey;
use Auth\Domain\Models\AdminUserPasskeyRepositoryInterface;
use DateTimeImmutable;
use Override;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Infrastructures\Database\QueryFactory;
use Support\Infrastructures\Database\Row;

readonly class AdminUserPasskeyRepository implements AdminUserPasskeyRepositoryInterface
{
    private const string TABLE = 'admin_user_passkeys';

    /** @var list<string> */
    private const array COLUMNS = [
        'admin_user_passkey_id',
        'admin_user_id',
        'user_handle',
        'name',
        'credential_id',
        'public_key',
        'aaguid',
        'transports',
        'backup_eligible',
        'backup_state',
        'sign_count',
        'created_at',
        'last_used_at',
    ];

    public function __construct(
        private QueryFactory $queryFactory,
        private UuidConverterInterface $converter,
    ) {
    }

    /**
     * @return list<AdminUserPasskey>
     */
    #[Override]
    public function findByAdminUserId(string $adminUserId): array
    {
        $rows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(self::COLUMNS)
                ->from(self::TABLE)
                ->where('admin_user_id', '=', $this->converter->toBin($adminUserId))
                ->orderBy('created_at'),
        );

        return array_map($this->hydrate(...), $rows);
    }

    #[Override]
    public function findByCredentialId(string $credentialId): ?AdminUserPasskey
    {
        $rows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(self::COLUMNS)
                ->from(self::TABLE)
                ->where('credential_id', '=', $credentialId)
                ->limit(1),
        );

        $row = $rows[0] ?? null;

        return is_null($row) ? null : $this->hydrate($row);
    }

    #[Override]
    public function findByUserHandle(string $userHandle): ?AdminUserPasskey
    {
        $rows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(self::COLUMNS)
                ->from(self::TABLE)
                ->where('user_handle', '=', $userHandle)
                ->orderBy('created_at')
                ->limit(1),
        );

        $row = $rows[0] ?? null;

        return is_null($row) ? null : $this->hydrate($row);
    }

    #[Override]
    public function findByAdminUserIdAndCredentialIdForUpdate(string $adminUserId, string $credentialId): ?AdminUserPasskey
    {
        $rows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(self::COLUMNS)
                ->from(self::TABLE)
                ->where('admin_user_id', '=', $this->converter->toBin($adminUserId))
                ->where('credential_id', '=', $credentialId)
                ->limit(1)
                ->forUpdate(),
        );

        $row = $rows[0] ?? null;

        return is_null($row) ? null : $this->hydrate($row);
    }

    #[Override]
    public function save(AdminUserPasskey $passkey): AdminUserPasskey
    {
        $now = now()->toDateTimeString();

        $this->queryFactory->insert()
            ->into(self::TABLE, [
                'admin_user_passkey_id',
                'admin_user_id',
                'user_handle',
                'name',
                'credential_id',
                'public_key',
                'aaguid',
                'transports',
                'backup_eligible',
                'backup_state',
                'sign_count',
                'last_used_at',
                'created_at',
                'updated_at',
            ])
            ->values([
                $this->converter->toBin($passkey->adminUserPasskeyId),
                $this->converter->toBin($passkey->adminUserId),
                $passkey->userHandle,
                $passkey->name,
                $passkey->credentialId,
                $passkey->publicKey,
                $passkey->aaguid,
                (string)json_encode($passkey->transports, JSON_THROW_ON_ERROR),
                $passkey->backupEligible,
                $passkey->backupState,
                $passkey->signCount,
                $passkey->lastUsedAt?->format('Y-m-d H:i:s'),
                $passkey->createdAt->format('Y-m-d H:i:s'),
                $now,
            ])
            ->execute($this->queryFactory->pdo());

        return $passkey;
    }

    #[Override]
    public function update(AdminUserPasskey $passkey): AdminUserPasskey
    {
        $this->queryFactory->update()
            ->table(self::TABLE)
            ->withSet([
                'sign_count' => $passkey->signCount,
                'last_used_at' => $passkey->lastUsedAt?->format('Y-m-d H:i:s'),
                'updated_at' => now()->toDateTimeString(),
            ])
            ->where('admin_user_passkey_id', '=', $this->converter->toBin($passkey->adminUserPasskeyId))
            ->execute($this->queryFactory->pdo());

        return $passkey;
    }

    #[Override]
    public function updateCounter(AdminUserPasskey $passkey, int $expectedSignCount): bool
    {
        $statement = $this->queryFactory->update()
            ->table(self::TABLE)
            ->withSet([
                'sign_count' => $passkey->signCount,
                'last_used_at' => $passkey->lastUsedAt?->format('Y-m-d H:i:s'),
                'updated_at' => now()->toDateTimeString(),
            ])
            ->where('admin_user_passkey_id', '=', $this->converter->toBin($passkey->adminUserPasskeyId))
            ->where('sign_count', '=', $expectedSignCount)
            ->prepare($this->queryFactory->pdo());

        $statement->execute();

        return $statement->rowCount() === 1;
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrate(array $row): AdminUserPasskey
    {
        $lastUsedAt = Row::nullableString($row, 'last_used_at');

        $transports = json_decode(Row::string($row, 'transports'), true, flags: JSON_THROW_ON_ERROR);

        return new AdminUserPasskey(
            $this->converter->toUuid(Row::string($row, 'admin_user_passkey_id')),
            $this->converter->toUuid(Row::string($row, 'admin_user_id')),
            Row::string($row, 'user_handle'),
            Row::string($row, 'name'),
            Row::string($row, 'credential_id'),
            Row::string($row, 'public_key'),
            Row::string($row, 'aaguid'),
            $this->transports($transports),
            Row::nullableBool($row, 'backup_eligible'),
            Row::nullableBool($row, 'backup_state'),
            Row::int($row, 'sign_count'),
            new DateTimeImmutable(Row::string($row, 'created_at')),
            is_null($lastUsedAt) ? null : new DateTimeImmutable($lastUsedAt),
        );
    }

    /**
     * @return list<string>
     */
    private function transports(mixed $transports): array
    {
        if (! is_array($transports)) {
            return [];
        }

        return array_filter($transports, static fn (mixed $transport): bool => is_string($transport))
            |> array_values(...);
    }
}
