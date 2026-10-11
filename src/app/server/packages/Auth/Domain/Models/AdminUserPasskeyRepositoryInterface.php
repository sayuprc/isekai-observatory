<?php

declare(strict_types=1);

namespace Auth\Domain\Models;

interface AdminUserPasskeyRepositoryInterface
{
    /**
     * @return list<AdminUserPasskey>
     */
    public function findByAdminUserId(string $adminUserId): array;

    public function findByCredentialId(string $credentialId): ?AdminUserPasskey;

    public function findByUserHandle(string $userHandle): ?AdminUserPasskey;

    public function findByAdminUserIdAndCredentialIdForUpdate(string $adminUserId, string $credentialId): ?AdminUserPasskey;

    public function save(AdminUserPasskey $passkey): AdminUserPasskey;

    public function update(AdminUserPasskey $passkey): AdminUserPasskey;

    public function updateCounter(AdminUserPasskey $passkey, int $expectedSignCount): bool;
}
