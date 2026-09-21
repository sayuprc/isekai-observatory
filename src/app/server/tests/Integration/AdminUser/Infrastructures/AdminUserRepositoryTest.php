<?php

declare(strict_types=1);

namespace Tests\Integration\AdminUser\Infrastructures;

use AdminUser\Domain\Models\AdminUserId;
use AdminUser\Domain\Models\Email;
use AdminUser\Domain\Models\Role;
use AdminUser\Infrastructures\AdminUserRepository;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\Database\CapturesQueries;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class AdminUserRepositoryTest extends DatabaseTestCase
{
    use CapturesQueries;
    use EntityFactory;

    #[Test]
    public function all(): void
    {
        $repository = $this->getInstance();

        $user1 = $this->createAdminUser($this->generateUuid(), 'user1@example.com', Role::General, [], new DateTimeImmutable('2026-01-01 00:00:00'));
        $user2 = $this->createAdminUser($this->generateUuid(), 'user2@example.com', Role::Privilege, [], new DateTimeImmutable('2026-01-02 00:00:00'));

        $repository->register($user1);
        $repository->register($user2);

        $users = $repository->all();

        $this->assertCount(2, $users);
        $this->assertEquals($user1, $users[0]);
        $this->assertEquals($user2, $users[1]);
    }

    #[Test]
    public function find(): void
    {
        $repository = $this->getInstance();

        $createdAt = new DateTimeImmutable('2026-01-01 00:00:00');
        $user = $this->createAdminUser($this->generateUuid(), 'user@example.com', Role::General, [], $createdAt);

        $repository->register($user);

        $found = $repository->find($user->adminUserId);

        $this->assertNotNull($found);
        $this->assertEquals($user, $found);
    }

    #[Test]
    public function findNotFound(): void
    {
        $found = $this->getInstance()->find(new AdminUserId($this->generateUuid()));

        $this->assertNull($found);
    }

    #[Test]
    public function findByEmail(): void
    {
        $repository = $this->getInstance();

        $createdAt = new DateTimeImmutable('2026-01-01 00:00:00');
        $user = $this->createAdminUser($this->generateUuid(), 'user@example.com', Role::General, [], $createdAt);

        $repository->register($user);

        $found = $repository->findByEmail($user->email);

        $this->assertNotNull($found);
        $this->assertEquals($user, $found);
    }

    #[Test]
    public function findByEmailNotFound(): void
    {
        $repository = $this->getInstance();

        $createdAt = new DateTimeImmutable('2026-01-01 00:00:00');
        $user = $this->createAdminUser($this->generateUuid(), 'user@example.com', Role::General, [], $createdAt);

        $found = $repository->findByEmail($user->email);

        $this->assertNull($found);
    }

    #[Test]
    public function findByEmailForUpdateIssuesSelectForUpdate(): void
    {
        $createdAt = new DateTimeImmutable('2026-01-01 00:00:00');
        $user = $this->createAdminUser($this->generateUuid(), 'user@example.com', Role::General, [], $createdAt);

        $this->getInstance()->register($user);

        $this->startCapturingQueries();

        $found = $this->getInstance()->findByEmailForUpdate(new Email('user@example.com'));

        $selectQueries = array_values(array_filter(
            $this->capturedQueries(),
            static fn (string $query): bool => str_starts_with(strtolower($query), 'select')
                && str_contains($query, 'admin_users'),
        ));

        $this->assertNotNull($found);
        $this->assertEquals($user, $found);
        $this->assertNotSame([], $selectQueries);
        $this->assertStringContainsString('for update', strtolower($selectQueries[0]));
    }

    #[Test]
    public function findByIdForUpdateIssuesSelectForUpdate(): void
    {
        $createdAt = new DateTimeImmutable('2026-01-01 00:00:00');
        $user = $this->createAdminUser($this->generateUuid(), 'user@example.com', Role::General, [], $createdAt);

        $this->getInstance()->register($user);

        $this->startCapturingQueries();

        $found = $this->getInstance()->findByIdForUpdate($user->adminUserId);

        $selectQueries = array_values(array_filter(
            $this->capturedQueries(),
            static fn (string $query): bool => str_starts_with(strtolower($query), 'select')
                && str_contains($query, 'admin_users'),
        ));

        $this->assertNotNull($found);
        $this->assertEquals($user, $found);
        $this->assertNotSame([], $selectQueries);
        $this->assertStringContainsString('for update', strtolower($selectQueries[0]));
    }

    #[Test]
    public function registerWithPermissions(): void
    {
        $repository = $this->getInstance();

        $createdAt = new DateTimeImmutable('2026-01-01 00:00:00');
        $user = $this->createAdminUser(
            $this->generateUuid(),
            'user@example.com',
            Role::General,
            ['read_person', 'write_person'],
            $createdAt,
        );

        $repository->register($user);

        $found = $repository->find($user->adminUserId);

        $this->assertNotNull($found);
        $this->assertEquals($user, $found);
    }

    #[Test]
    public function registerWithoutPasswordColumn(): void
    {
        $repository = $this->getInstance();

        $createdAt = new DateTimeImmutable('2026-01-01 00:00:00');
        $user = $this->createAdminUser($this->generateUuid(), 'user@example.com', Role::General, [], $createdAt);

        $repository->register($user);

        $found = $repository->find($user->adminUserId);
        $stored = DB::table('admin_users')
            ->where('email', $user->email->value)
            ->first();

        $this->assertNotNull($found);
        $this->assertEquals($user, $found);
        $this->assertNotNull($stored);
        $this->assertArrayNotHasKey('password', (array)$stored);
    }

    private function getInstance(): AdminUserRepository
    {
        return $this->app->make(AdminUserRepository::class);
    }
}
