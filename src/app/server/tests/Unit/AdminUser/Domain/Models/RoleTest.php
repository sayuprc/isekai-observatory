<?php

declare(strict_types=1);

namespace Tests\Unit\AdminUser\Domain\Models;

use AdminUser\Domain\Models\Role;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RoleTest extends TestCase
{
    #[Test]
    #[DataProvider('getNameDataProvider')]
    public function getName(Role $role, string $expected): void
    {
        $this->assertSame($expected, $role->getName());
    }

    public static function getNameDataProvider(): array
    {
        return [
            [Role::Privilege, '特権'],
            [Role::Console, 'コンソール'],
            [Role::General, '一般'],
        ];
    }

    #[Test]
    #[DataProvider('isPrivilegeDataProvider')]
    public function isPrivilege(Role $role, bool $expected): void
    {
        $this->assertSame($expected, $role->isPrivilege());
    }

    public static function isPrivilegeDataProvider(): array
    {
        return [
            [Role::Privilege, true],
            [Role::Console, true],
            [Role::General, false],
        ];
    }
}
