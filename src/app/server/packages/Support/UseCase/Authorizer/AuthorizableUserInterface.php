<?php

declare(strict_types=1);

namespace Support\UseCase\Authorizer;

use AdminUser\Domain\Models\Permission;

interface AuthorizableUserInterface
{
    public function can(Permission $permission): bool;
}
