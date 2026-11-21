<?php

declare(strict_types=1);

namespace Support\UseCase\Authorizer;

use Support\Domain\Permission;
use Support\Domain\ValueObjects\String\UuidValueObject;

interface AuthorizableUserInterface
{
    public function id(): UuidValueObject;

    public function can(Permission $permission): bool;
}
