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

class GetAuditLogTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;
    use SeedsAuditLog;
    use WithAuth;

    #[Test]
    public function found(): void
    {
        $actorId = $this->generateUuid();
        $this->storeAdminUsers($this->createAdminUser($actorId, 'actor@example.com', Role::Privilege, name: '監査テストユーザーA'));

        $auditLogId = $this->generateUuid();
        $targetId = $this->generateUuid();

        $snapshot = [
            'title' => 'テスト楽曲',
            'tags' => ['オリジナル', '感動'],
            'meta' => ['version' => 3, 'isDisplay' => true],
        ];

        $this->insertAuditLog(
            $auditLogId,
            $actorId,
            AuditAction::Update,
            AuditTargetType::Song,
            $targetId,
            $snapshot,
            new DateTimeImmutable('2026-04-02 10:00:00'),
        );

        $response = $this->withAuth()
            ->get(route(AuditLogRouteMap::Get, $auditLogId))
            ->assertStatus(200)
            ->json();
        /** @var array{auditLog: array{auditLogId: string, adminUserId: string, adminUserName: string, action: string, targetType: string, targetId: string, createdAt: string, snapshot: array{title: string, tags: array<int, string>, meta: array{version: int, isDisplay: bool}}}} $response */
        $this->assertArrayHasKey('auditLog', $response);
        $this->assertSame($auditLogId, $response['auditLog']['auditLogId']);
        $this->assertSame($actorId, $response['auditLog']['adminUserId']);
        $this->assertSame('監査テストユーザーA', $response['auditLog']['adminUserName']);
        $this->assertSame('update', $response['auditLog']['action']);
        $this->assertSame('Song', $response['auditLog']['targetType']);
        $this->assertSame($targetId, $response['auditLog']['targetId']);
        $this->assertArrayHasKey('createdAt', $response['auditLog']);
        $this->assertSame('テスト楽曲', $response['auditLog']['snapshot']['title']);
        $this->assertSame(['オリジナル', '感動'], $response['auditLog']['snapshot']['tags']);
        $this->assertSame(3, $response['auditLog']['snapshot']['meta']['version']);
        $this->assertTrue($response['auditLog']['snapshot']['meta']['isDisplay']);
    }

    #[Test]
    public function foundForRelease(): void
    {
        $actorId = $this->generateUuid();
        $this->storeAdminUsers($this->createAdminUser($actorId, 'actor@example.com', Role::Privilege, name: '監査テストユーザーA'));

        $auditLogId = $this->generateUuid();
        $targetId = $this->generateUuid();

        $this->insertAuditLog(
            $auditLogId,
            $actorId,
            AuditAction::Update,
            AuditTargetType::Release,
            $targetId,
            ['title' => '観測された春'],
            new DateTimeImmutable('2026-04-02 10:00:00'),
        );

        $response = $this->withAuth()
            ->get(route(AuditLogRouteMap::Get, $auditLogId))
            ->assertStatus(200)
            ->json();
        /** @var array{auditLog: array{targetType: string, snapshot: array{title: string}}} $response */
        $this->assertSame('Release', $response['auditLog']['targetType']);
        $this->assertSame('観測された春', $response['auditLog']['snapshot']['title']);
    }

    #[Test]
    public function returnsForbiddenWhenLackingPermission(): void
    {
        $actorId = $this->generateUuid();
        $this->storeAdminUsers($this->createAdminUser($actorId, 'actor@example.com', Role::Privilege));

        $auditLogId = $this->generateUuid();

        $this->insertAuditLog(
            $auditLogId,
            $actorId,
            AuditAction::Update,
            AuditTargetType::Song,
            $this->generateUuid(),
            ['title' => 'テスト楽曲'],
            new DateTimeImmutable('2026-04-02 10:00:00'),
        );

        $this->withGeneralAuth()
            ->get(route(AuditLogRouteMap::Get, $auditLogId))
            ->assertStatus(403);
    }

    #[Test]
    public function notFound(): void
    {
        $auditLogId = $this->generateUuid();

        $this->withAuth()
            ->get(route(AuditLogRouteMap::Get, $auditLogId))
            ->assertStatus(404);
    }
}
