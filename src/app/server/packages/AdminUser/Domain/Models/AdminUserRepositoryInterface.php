<?php

declare(strict_types=1);

namespace AdminUser\Domain\Models;

interface AdminUserRepositoryInterface
{
    /**
     * @return array<AdminUser>
     */
    public function all(): array;

    public function find(AdminUserId $adminUserId): ?AdminUser;

    public function findByIdForUpdate(AdminUserId $adminUserId): ?AdminUser;

    public function findByEmail(Email $email): ?AdminUser;

    public function findByEmailForUpdate(Email $email): ?AdminUser;

    public function register(AdminUser $adminUser): AdminUser;
}
