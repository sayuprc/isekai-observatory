<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\AdminUser;

use AdminUser\Domain\Models\AdminUser;
use AdminUser\Domain\Models\Permission;
use Carbon\Carbon;
use DateTime;
use DateTimeImmutable;
use OpenAPI\Admin\Client\Model\AdminUser as OpenApiAdminUser;
use OpenAPI\Admin\Client\Model\PermissionValue;
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
            ->setRole(RoleValue::from($adminUser->role->value))
            ->setPermissions($adminUser->permissions->toGeneric()->map(static fn (Permission $permission): PermissionValue => PermissionValue::from($permission->value))->toArray());
    }

    private function toDateTime(DateTimeImmutable $dateTime): DateTime
    {
        return new Carbon($dateTime)->toDateTime();
    }
}
