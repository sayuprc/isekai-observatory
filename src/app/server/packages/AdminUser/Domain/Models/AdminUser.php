<?php

declare(strict_types=1);

namespace AdminUser\Domain\Models;

use DateTimeImmutable;

readonly class AdminUser
{
    public function __construct(
        public AdminUserId $adminUserId,
        public AdminUserName $name,
        public Email $email,
        public CreatedAt $createdAt,
        public Role $role,
        public Permissions $permissions,
    ) {
    }

    /**
     * @param list<string> $permissions
     */
    public static function reconstruct(
        string $adminUserId,
        string $name,
        string $email,
        DateTimeImmutable $createdAt,
        int $role,
        array $permissions,
    ): self {
        return new self(
            new AdminUserId($adminUserId),
            new AdminUserName($name),
            new Email($email),
            new CreatedAt($createdAt),
            Role::from($role),
            Permissions::reconstruct($permissions),
        );
    }

    /**
     * @return array{admin_user_id: string, name: string, email: string, created_at: string, role: value-of<Role>, permissions: list<string>}
     */
    public function toArray(): array
    {
        return [
            'admin_user_id' => $this->adminUserId->value,
            'name' => $this->name->value,
            'email' => $this->email->value,
            'created_at' => $this->createdAt->value->format('Y-m-d H:i:s'),
            'role' => $this->role->value,
            'permissions' => $this->permissions->toArray(),
        ];
    }

    public function equals(self $other): bool
    {
        return $this->adminUserId->equals($other->adminUserId);
    }

    public function can(Permission $permission): bool
    {
        return $this->role->isPrivilege() || $this->permissions->has($permission);
    }
}
