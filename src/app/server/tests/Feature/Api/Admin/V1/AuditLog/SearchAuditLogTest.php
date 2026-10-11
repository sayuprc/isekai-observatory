<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\AuditLog;

use AdminUser\Domain\Models\Role;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use Support\Route\AuditLogRouteMap;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditTargetType;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class SearchAuditLogTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;
    use SeedsAuditLog;
    use WithAuth;

    #[Test]
    public function returnsListWithPermission(): void
    {
        $actorId = $this->generateUuid();
        $this->storeAdminUsers($this->createAdminUser($actorId, 'actor@example.com', Role::Privilege, name: '監査テストユーザーA'));

        $auditLogId = $this->generateUuid();
        $targetId = $this->generateUuid();

        $this->insertAuditLog(
            $auditLogId,
            $actorId,
            AuditAction::Update,
            AuditTargetType::Song,
            $targetId,
            ['title' => 'テスト楽曲'],
            new DateTimeImmutable('2026-04-02 10:00:00'),
        );

        $response = $this->withAuth()
            ->get(route(AuditLogRouteMap::Search))
            ->assertStatus(200)
            ->json();
        /** @var array{maxPage: int, auditLogs: list<array{auditLogId: string, adminUserId: string, adminUserName: string, action: string, targetType: string, targetId: string}>} $response */
        $this->assertSame(1, $response['maxPage']);
        $this->assertCount(1, $response['auditLogs']);
        $this->assertSame($auditLogId, $response['auditLogs'][0]['auditLogId']);
        $this->assertSame($actorId, $response['auditLogs'][0]['adminUserId']);
        $this->assertSame('監査テストユーザーA', $response['auditLogs'][0]['adminUserName']);
        $this->assertSame('update', $response['auditLogs'][0]['action']);
        $this->assertSame('Song', $response['auditLogs'][0]['targetType']);
        $this->assertSame($targetId, $response['auditLogs'][0]['targetId']);
    }

    #[Test]
    public function canFilterReleaseTargetType(): void
    {
        $actorId = $this->generateUuid();
        $this->storeAdminUsers($this->createAdminUser($actorId, 'actor@example.com', Role::Privilege, name: '監査テストユーザーA'));

        $releaseAuditLogId = $this->generateUuid();

        $this->insertAuditLog(
            $releaseAuditLogId,
            $actorId,
            AuditAction::Update,
            AuditTargetType::Release,
            $this->generateUuid(),
            ['title' => '観測された春'],
            new DateTimeImmutable('2026-04-02 10:00:00'),
        );
        $this->insertAuditLog(
            $this->generateUuid(),
            $actorId,
            AuditAction::Update,
            AuditTargetType::Song,
            $this->generateUuid(),
            ['title' => 'テスト楽曲'],
            new DateTimeImmutable('2026-04-03 10:00:00'),
        );

        $response = $this->withAuth()
            ->get(route(AuditLogRouteMap::Search, ['target_type' => 'Release']))
            ->assertStatus(200)
            ->json();
        /** @var array{auditLogs: list<array{auditLogId: string, targetType: string}>} $response */
        $this->assertCount(1, $response['auditLogs']);
        $this->assertSame($releaseAuditLogId, $response['auditLogs'][0]['auditLogId']);
        $this->assertSame('Release', $response['auditLogs'][0]['targetType']);
    }

    #[Test]
    public function canFilterRegisterAction(): void
    {
        $actorId = $this->generateUuid();
        $this->storeAdminUsers($this->createAdminUser($actorId, 'actor@example.com', Role::Privilege, name: '監査テストユーザーA'));

        $registerAuditLogId = $this->generateUuid();

        $this->insertAuditLog(
            $registerAuditLogId,
            $actorId,
            AuditAction::Register,
            AuditTargetType::AdminUser,
            $actorId,
            ['refresh_token_id' => $this->generateUuid()],
            new DateTimeImmutable('2026-04-02 10:00:00'),
        );
        $this->insertAuditLog(
            $this->generateUuid(),
            $actorId,
            AuditAction::Login,
            AuditTargetType::AdminUser,
            $actorId,
            ['refresh_token_id' => $this->generateUuid()],
            new DateTimeImmutable('2026-04-03 10:00:00'),
        );

        $response = $this->withAuth()
            ->get(route(AuditLogRouteMap::Search, ['action' => 'register']))
            ->assertStatus(200)
            ->json();
        /** @var array{auditLogs: list<array{auditLogId: string, action: string}>} $response */
        $this->assertCount(1, $response['auditLogs']);
        $this->assertSame($registerAuditLogId, $response['auditLogs'][0]['auditLogId']);
        $this->assertSame('register', $response['auditLogs'][0]['action']);
    }

    #[Test]
    public function filtersByAdminUserNameWithPartialMatch(): void
    {
        $actor1Id = $this->generateUuid();
        $actor2Id = $this->generateUuid();
        $this->storeAdminUsers(
            $this->createAdminUser($actor1Id, 'actor1@example.com', Role::Privilege, name: '監査テストユーザーA'),
            $this->createAdminUser($actor2Id, 'actor2@example.com', Role::Privilege, name: '検索テストユーザーB'),
        );

        $this->insertAuditLog(
            $this->generateUuid(),
            $actor1Id,
            AuditAction::Update,
            AuditTargetType::Song,
            $this->generateUuid(),
            ['title' => 'A'],
            new DateTimeImmutable('2026-04-01 10:00:00'),
        );
        $this->insertAuditLog(
            $this->generateUuid(),
            $actor2Id,
            AuditAction::Update,
            AuditTargetType::Song,
            $this->generateUuid(),
            ['title' => 'B'],
            new DateTimeImmutable('2026-04-02 10:00:00'),
        );

        $response = $this->withAuth()
            ->get(route(AuditLogRouteMap::Search, ['admin_user_name' => '監査']))
            ->assertStatus(200)
            ->json();
        /** @var array{auditLogs: list<array{adminUserName: string}>} $response */
        $this->assertCount(1, $response['auditLogs']);
        $this->assertSame('監査テストユーザーA', $response['auditLogs'][0]['adminUserName']);
    }

    #[Test]
    public function returnsForbiddenWhenLackingPermission(): void
    {
        $this->withGeneralAuth()
            ->get(route(AuditLogRouteMap::Search))
            ->assertStatus(403);
    }
}
