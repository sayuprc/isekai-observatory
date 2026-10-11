<?php

declare(strict_types=1);

namespace Auth\Infrastructures\Auth;

use AdminUser\Domain\Models\AdminUser;
use AdminUser\Domain\Models\Permission;
use Support\UseCase\Authorizer\AuthorizableUserInterface;

readonly class AuthorizedAdminUser implements AuthorizableUserInterface
{
    public function __construct(private AdminUser $user)
    {
    }

    public function can(Permission $permission): bool
    {
        return $this->user->can($permission);
    }
}
