<?php

declare(strict_types=1);

namespace Auth\Domain\Models\RecoveryCode;

use AdminUser\Domain\Models\AdminUserId;

interface RecoveryCodeRepositoryInterface
{
    /**
     * @param list<RecoveryCode> $codes
     */
    public function saveMany(array $codes): void;

    /**
     * @return list<RecoveryCode>
     */
    public function findUnusedByAdminUserIdForUpdate(AdminUserId $adminUserId): array;

    public function findUnusedByIdForUpdate(RecoveryCodeId $recoveryCodeId, AdminUserId $adminUserId): ?RecoveryCode;

    public function save(RecoveryCode $code): RecoveryCode;

    public function deleteByAdminUserId(AdminUserId $adminUserId): void;
}
