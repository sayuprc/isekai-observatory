<?php

declare(strict_types=1);

namespace Tests\Integration\Auth\Infrastructures;

use AdminUser\Domain\Models\Role;
use AdminUser\Infrastructures\AdminUserRepository;
use Auth\Domain\Models\AdminUserPasskey;
use Auth\Infrastructures\AdminUserPasskeyRepository;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\Database\CapturesQueries;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class AdminUserPasskeyRepositoryTest extends DatabaseTestCase
{
    use CapturesQueries;
    use EntityFactory;

    #[Test]
    public function saveAndFindAndUpdate(): void
    {
        $adminUserId = $this->generateUuid();
        $this->app->make(AdminUserRepository::class)->register(
            $this->createAdminUser($adminUserId, 'user@example.com', Role::General),
        );

        $repository = $this->app->make(AdminUserPasskeyRepository::class);
        $passkey = new AdminUserPasskey(
            $this->generateUuid(),
            $adminUserId,
            'user-handle',
            'Primary passkey',
            'credential-id',
            'public-key',
            '00000000-0000-0000-0000-000000000000',
            ['internal', 'hybrid'],
            true,
            false,
            1,
            new DateTimeImmutable('2026-01-01 00:00:00'),
            null,
        );

        $repository->save($passkey);

        $this->assertEquals($passkey, $repository->findByCredentialId('credential-id'));
        $this->assertEquals($passkey, $repository->findByUserHandle('user-handle'));
        $this->assertEquals([$passkey], $repository->findByAdminUserId($adminUserId));

        $updated = $passkey->withCounter(2, new DateTimeImmutable('2026-01-02 00:00:00'));
        $repository->update($updated);

        $found = $repository->findByCredentialId('credential-id');
        $this->assertNotNull($found);
        $this->assertSame(2, $found->signCount);
        $this->assertEquals(new DateTimeImmutable('2026-01-02 00:00:00'), $found->lastUsedAt);
    }

    #[Test]
    public function findByAdminUserIdAndCredentialIdForUpdate(): void
    {
        $adminUserId = $this->generateUuid();
        $otherAdminUserId = $this->generateUuid();
        $this->app->make(AdminUserRepository::class)->register(
            $this->createAdminUser($adminUserId, 'user@example.com', Role::General),
        );
        $this->app->make(AdminUserRepository::class)->register(
            $this->createAdminUser($otherAdminUserId, 'other@example.com', Role::General),
        );

        $repository = $this->app->make(AdminUserPasskeyRepository::class);
        $passkey = new AdminUserPasskey(
            $this->generateUuid(),
            $adminUserId,
            'user-handle',
            'Primary passkey',
            'credential-id',
            'public-key',
            '00000000-0000-0000-0000-000000000000',
            ['internal'],
            true,
            false,
            1,
            new DateTimeImmutable('2026-01-01 00:00:00'),
            null,
        );

        $repository->save($passkey);

        $this->assertEquals($passkey, $repository->findByAdminUserIdAndCredentialIdForUpdate($adminUserId, 'credential-id'));
        $this->assertNull($repository->findByAdminUserIdAndCredentialIdForUpdate($otherAdminUserId, 'credential-id'));
    }

    #[Test]
    public function findByAdminUserIdAndCredentialIdForUpdateIssuesSelectForUpdate(): void
    {
        $adminUserId = $this->generateUuid();
        $this->app->make(AdminUserRepository::class)->register(
            $this->createAdminUser($adminUserId, 'user@example.com', Role::General),
        );

        $this->app->make(AdminUserPasskeyRepository::class)->save(new AdminUserPasskey(
            $this->generateUuid(),
            $adminUserId,
            'user-handle',
            'Primary passkey',
            'credential-id',
            'public-key',
            '00000000-0000-0000-0000-000000000000',
            ['internal'],
            true,
            false,
            1,
            new DateTimeImmutable('2026-01-01 00:00:00'),
            null,
        ));

        $this->startCapturingQueries();

        $this->app->make(AdminUserPasskeyRepository::class)
            ->findByAdminUserIdAndCredentialIdForUpdate($adminUserId, 'credential-id');

        $selectQueries = array_values(array_filter(
            $this->capturedQueries(),
            static fn (string $query): bool => str_starts_with(strtolower($query), 'select')
                && str_contains($query, 'admin_user_passkeys'),
        ));

        $this->assertNotSame([], $selectQueries);
        $this->assertStringContainsString('for update', strtolower($selectQueries[0]));
    }

    #[Test]
    public function updateCounterReturnsFalseWhenExpectedSignCountDoesNotMatch(): void
    {
        $adminUserId = $this->generateUuid();
        $this->app->make(AdminUserRepository::class)->register(
            $this->createAdminUser($adminUserId, 'user@example.com', Role::General),
        );

        $repository = $this->app->make(AdminUserPasskeyRepository::class);
        $passkey = new AdminUserPasskey(
            $this->generateUuid(),
            $adminUserId,
            'user-handle',
            'Primary passkey',
            'credential-id',
            'public-key',
            '00000000-0000-0000-0000-000000000000',
            ['internal'],
            true,
            false,
            1,
            new DateTimeImmutable('2026-01-01 00:00:00'),
            null,
        );

        $repository->save($passkey);

        $updated = $passkey->withCounter(2, new DateTimeImmutable('2026-01-02 00:00:00'));

        $this->assertFalse($repository->updateCounter($updated, 999));
        $this->assertSame(1, $repository->findByCredentialId('credential-id')?->signCount);
        $this->assertTrue($repository->updateCounter($updated, 1));
        $this->assertSame(2, $repository->findByCredentialId('credential-id')?->signCount);
    }
}
