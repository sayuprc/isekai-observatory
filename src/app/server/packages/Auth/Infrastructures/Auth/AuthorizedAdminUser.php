<?php

declare(strict_types=1);

namespace Auth\Infrastructures\Auth;

use AdminUser\Domain\Models\AdminUser;
use AdminUser\Domain\Models\AdminUserId;
use Support\Domain\Permission;
use Support\UseCase\Authorizer\AuthorizableUserInterface;

readonly class AuthorizedAdminUser implements AuthorizableUserInterface
{
    public function __construct(private AdminUser $user)
    {
    }

    public function id(): AdminUserId
    {
        return $this->user->adminUserId;
    }

    public function can(Permission $permission): bool
    {
        return $this->user->can($permission);
    }
}
