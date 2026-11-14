<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\ReleaseGroup;

use PHPUnit\Framework\Attributes\Test;
use Release\Domain\Models\ReleaseGroupId;
use Release\Domain\Models\ReleaseGroupType;
use Release\Infrastructures\ReleaseGroupRepository;
use Release\Route\ReleaseGroupRouteMap;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditTargetType;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\Concerns\AssertsAuditLog;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class BulkUpdateReleaseGroupTest extends DatabaseTestCase
{
    use AssertsAuditLog;
    use EntityFactory;
    use EntityStore;
    use WithAuth;

    #[Test]
    public function canAddAndRemoveTagsAndSetDisplay(): void
    {
        $addTagId = $this->generateUuid();
        $removeTagId = $this->generateUuid();
        $first = $this->generateUuid();
        $second = $this->generateUuid();
        $this->storeTags($this->createTag($addTagId, 'タグA', 10), $this->createTag($removeTagId, 'タグB', 20));
        $this->storeReleaseGroups(
            $this->createReleaseGroup($first, 'リリース', ReleaseGroupType::Album, true, tags: [[
                'tagId' => $removeTagId,
            ]]),
            $this->createReleaseGroup($second, 'リリース', ReleaseGroupType::Album, true, tags: []),
        );

        $this
            ->withAuth()
            ->postJson(route(ReleaseGroupRouteMap::BulkUpdate), [
                'releaseGroupIds' => [$first, $second],
                'addTagIds' => [$addTagId],
                'removeTagIds' => [$removeTagId],
                'isDisplay' => false,
            ])
            ->assertOk()
            ->assertExactJson(['updatedCount' => 2]);

        $repository = $this->app->make(ReleaseGroupRepository::class);

        foreach ([$first, $second] as $id) {
            $record = $repository->find(new ReleaseGroupId($id));
            $this->assertFalse($record?->isDisplay);
            $this->assertSame([$addTagId], array_column($record?->tags->toArray() ?? [], 'tag_id'));
        }

        $this->assertAuditLogCount(2);
        $this->findAuditLog(AuditAction::Update, AuditTargetType::ReleaseGroup, $first);
        $this->findAuditLog(AuditAction::Update, AuditTargetType::ReleaseGroup, $second);
    }

    #[Test]
    public function nothingChangesWhenAnyRecordDoesNotExist(): void
    {
        $existing = $this->generateUuid();
        $this->storeReleaseGroups($this->createReleaseGroup(
            $existing,
            'リリース',
            ReleaseGroupType::Album,
            true,
            tags: [],
        ));

        $this
            ->withAuth()
            ->postJson(route(ReleaseGroupRouteMap::BulkUpdate), [
                'releaseGroupIds' => [$existing, $this->generateUuid()],
                'isDisplay' => false,
            ])
            ->assertNotFound();

        $record = $this->app->make(ReleaseGroupRepository::class)->find(new ReleaseGroupId($existing));
        $this->assertTrue($record?->isDisplay);
    }

    #[Test]
    public function generalUserCannotBulkUpdate(): void
    {
        $id = $this->generateUuid();
        $this->storeReleaseGroups($this->createReleaseGroup($id, 'リリース', ReleaseGroupType::Album, true, tags: []));

        $this
            ->withGeneralAuth()
            ->postJson(route(ReleaseGroupRouteMap::BulkUpdate), ['releaseGroupIds' => [$id], 'isDisplay' => false])
            ->assertForbidden();
    }
}
