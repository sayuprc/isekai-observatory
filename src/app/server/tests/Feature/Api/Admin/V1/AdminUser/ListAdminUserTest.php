<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\AdminUser;

use AdminUser\Domain\Models\Role;
use AdminUser\Infrastructures\AdminUserRepository;
use AdminUser\Route\AdminUserRouteMap;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class ListAdminUserTest extends DatabaseTestCase
{
    use EntityFactory;
    use WithAuth;

    #[Test]
    public function showList(): void
    {
        $uuid = $this->generateUuid();

        $this->app->make(AdminUserRepository::class)->register(
            $this->createAdminUser(
                $uuid,
                'admin@example.com',
                Role::Console,
                [],
                new DateTimeImmutable('2019-12-09 10:20:30'),
                'コンソールユーザー',
            ),
        );

        $response = $this->withAuth()
            ->get(route(AdminUserRouteMap::List))
            ->assertStatus(200);

        $json = $response->json();
        $authUser = collect($json['adminUsers'])->firstWhere('email', 'root@example.com');

        $response->assertExactJson([
            'adminUsers' => [
                [
                    'adminUserId' => $uuid,
                    'name' => 'コンソールユーザー',
                    'email' => 'admin@example.com',
                    'createdAt' => '2019-12-09T10:20:30+09:00',
                    'role' => [
                        'name' => Role::Console->getName(),
                        'value' => Role::Console->value,
                    ],
                    'permissions' => [],
                ],
                [
                    'adminUserId' => $authUser['adminUserId'],
                    'name' => 'テストユーザー',
                    'email' => 'root@example.com',
                    'createdAt' => $authUser['createdAt'],
                    'role' => [
                        'name' => Role::Privilege->getName(),
                        'value' => Role::Privilege->value,
                    ],
                    'permissions' => [],
                ],
            ],
        ]);
    }
}
