<?php

declare(strict_types=1);

namespace Tests\Unit\AdminUser\Domain\Services;

use AdminUser\Domain\Models\AdminUserId;
use AdminUser\Domain\Models\AdminUserName;
use AdminUser\Domain\Models\AdminUserRepositoryInterface;
use AdminUser\Domain\Models\Email;
use AdminUser\Domain\Models\Permissions;
use AdminUser\Domain\Models\Role;
use AdminUser\Domain\Services\AdminUserIntegrityService;
use DateTimeImmutable;
use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\ClockInterface;
use Support\Contracts\Uuid\UuidGeneratorInterface;
use Support\Domain\Exceptions\BusinessRuleViolationException;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class AdminUserIntegrityServiceTest extends TestCase
{
    use EntityFactory;

    private ClockInterface&MockInterface $clock;

    private MockInterface&UuidGeneratorInterface $generator;

    private AdminUserRepositoryInterface&MockInterface $repository;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->clock = Mockery::mock(ClockInterface::class);
        $this->generator = Mockery::mock(UuidGeneratorInterface::class);
        $this->repository = Mockery::mock(AdminUserRepositoryInterface::class);
    }

    #[Test]
    public function prepareForCreate(): void
    {
        $adminUserName = 'テストユーザー';
        $email = 'example@example.com';
        $uuid = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $now = new DateTimeImmutable('2026-01-01 00:00:00');
        $role = Role::General;
        $permissions = [];

        $this->generator->shouldReceive('generate')
            ->with()
            ->andReturn($uuid)
            ->once();

        $this->clock->shouldReceive('now')
            ->with()
            ->andReturn($now)
            ->once();

        $expectedUser = $this->createAdminUser($uuid, $email, $role, $permissions, $now);

        $this->repository->shouldReceive('findByEmail')
            ->with(Mockery::on(static fn (Email $arg): bool => $arg->value === $email))
            ->andReturnNull()
            ->once();

        $actual = $this->getInstance()->prepareForCreate(
            new AdminUserName($adminUserName),
            new Email($email),
            $role,
            Permissions::fromArray($permissions),
        );

        $this->assertEquals($expectedUser, $actual);
    }

    #[Test]
    public function prepareForCreateDuplicateEmail(): void
    {
        $adminUserName = 'テストユーザー';
        $email = 'example@example.com';
        $uuid = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $now = new DateTimeImmutable('2026-01-01 00:00:00');
        $role = Role::General;
        $permissions = [];

        $existingUser = $this->createAdminUser('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', $email, $role, $permissions, $now);

        $this->repository->shouldReceive('findByEmail')
            ->with(Mockery::on(static fn (Email $arg): bool => $arg->value === $email))
            ->andReturn($existingUser)
            ->once();

        $this->generator->shouldReceive('generate')->never();
        $this->clock->shouldReceive('now')->never();

        $this->expectException(BusinessRuleViolationException::class);
        $this->expectExceptionMessage('すでに使われているメールアドレスです "example@example.com"');

        $this->getInstance()->prepareForCreate(
            new AdminUserName($adminUserName),
            new Email($email),
            $role,
            Permissions::fromArray($permissions),
        );
    }

    #[Test]
    public function prepareForCreateWithIdUsesLockedEmailLookup(): void
    {
        $adminUserName = 'テストユーザー';
        $email = 'example@example.com';
        $uuid = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $now = new DateTimeImmutable('2026-01-01 00:00:00');
        $role = Role::General;
        $permissions = [];

        $this->clock->shouldReceive('now')
            ->with()
            ->andReturn($now)
            ->once();

        $expectedUser = $this->createAdminUser($uuid, $email, $role, $permissions, $now);

        $this->repository->shouldReceive('findByEmailForUpdate')
            ->with(Mockery::on(static fn (Email $arg): bool => $arg->value === $email))
            ->andReturnNull()
            ->once();

        $actual = $this->getInstance()->prepareForCreateWithId(
            new AdminUserId($uuid),
            new AdminUserName($adminUserName),
            new Email($email),
            $role,
            Permissions::fromArray($permissions),
        );

        $this->assertEquals($expectedUser, $actual);
    }

    #[Test]
    public function prepareForCreateWithIdDuplicateEmail(): void
    {
        $adminUserName = 'テストユーザー';
        $email = 'example@example.com';
        $uuid = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $now = new DateTimeImmutable('2026-01-01 00:00:00');
        $role = Role::General;
        $permissions = [];

        $existingUser = $this->createAdminUser('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', $email, $role, $permissions, $now);

        $this->repository->shouldReceive('findByEmailForUpdate')
            ->with(Mockery::on(static fn (Email $arg): bool => $arg->value === $email))
            ->andReturn($existingUser)
            ->once();

        $this->clock->shouldReceive('now')->never();

        $this->expectException(BusinessRuleViolationException::class);
        $this->expectExceptionMessage('すでに使われているメールアドレスです "example@example.com"');

        $this->getInstance()->prepareForCreateWithId(
            new AdminUserId($uuid),
            new AdminUserName($adminUserName),
            new Email($email),
            $role,
            Permissions::fromArray($permissions),
        );
    }

    private function getInstance(): AdminUserIntegrityService
    {
        return new AdminUserIntegrityService(
            $this->clock,
            $this->generator,
            $this->repository,
        );
    }
}
