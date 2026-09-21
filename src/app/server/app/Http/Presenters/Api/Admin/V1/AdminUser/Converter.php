<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\AdminUser;

use AdminUser\Domain\Models\AdminUser;
use AdminUser\Domain\Models\Permission;
use AdminUser\Domain\Models\Role;
use Carbon\Carbon;
use DateTime;
use DateTimeImmutable;
use OpenAPI\Admin\Client\Model\AdminUser as OpenApiAdminUser;
use OpenAPI\Admin\Client\Model\Permission as OpenApiPermission;
use OpenAPI\Admin\Client\Model\PermissionValue;
use OpenAPI\Admin\Client\Model\Role as OpenApiRole;
use OpenAPI\Admin\Client\Model\RoleValue;

class Converter
{
    public function toOpenApiAdminUser(AdminUser $adminUser): OpenApiAdminUser
    {
        return new OpenApiAdminUser()
            ->setAdminUserId($adminUser->adminUserId->value)
            ->setName($adminUser->name->value)
            ->setEmail($adminUser->email->value)
            ->setCreatedAt($this->toDateTime($adminUser->createdAt->value))
            ->setRole($this->toOpenApiRole($adminUser->role))
            ->setPermissions($adminUser->permissions->toGeneric()->map($this->toOpenApiPermission(...))->toArray());
    }

    private function toDateTime(DateTimeImmutable $dateTime): DateTime
    {
        return new Carbon($dateTime)->toDateTime();
    }

    private function toOpenApiRole(Role $role): OpenApiRole
    {
        return new OpenApiRole()
            ->setName($role->getName())
            ->setValue(RoleValue::from($role->value));
    }

    private function toOpenApiPermission(Permission $permission): OpenApiPermission
    {
        return new OpenApiPermission()
            ->setName($permission->getName())
            ->setValue(PermissionValue::from($permission->value));
    }
}
