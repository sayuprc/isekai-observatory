<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Event;

use Event\Domain\Models\EventId;
use Event\Infrastructures\EventRepository;
use Event\Route\EventRouteMap;
use PHPUnit\Framework\Attributes\Test;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditTargetType;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\Concerns\AssertsAuditLog;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class BulkUpdateEventTest extends DatabaseTestCase
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
        $this->storeEvents(
            $this->createEvent($first, tags: [['tagId' => $removeTagId]]),
            $this->createEvent($second, tags: []),
        );

        $this
            ->withAuth()
            ->postJson(route(EventRouteMap::BulkUpdate), [
                'eventIds' => [$first, $second],
                'addTagIds' => [$addTagId],
                'removeTagIds' => [$removeTagId],
                'isDisplay' => false,
            ])
            ->assertStatus(200)
            ->assertExactJson(['updatedCount' => 2]);

        $repository = $this->app->make(EventRepository::class);

        foreach ([$first, $second] as $id) {
            $record = $repository->find(new EventId($id));
            $this->assertFalse($record?->isDisplay);
            $this->assertSame([$addTagId], array_column($record?->tags->toArray() ?? [], 'tag_id'));
        }

        $this->assertAuditLogCount(2);
        $this->findAuditLog(AuditAction::Update, AuditTargetType::Event, $first);
        $this->findAuditLog(AuditAction::Update, AuditTargetType::Event, $second);
    }

    #[Test]
    public function nothingChangesWhenAnyRecordDoesNotExist(): void
    {
        $existing = $this->generateUuid();
        $this->storeEvents($this->createEvent($existing, tags: []));

        $this
            ->withAuth()
            ->postJson(route(EventRouteMap::BulkUpdate), [
                'eventIds' => [$existing, $this->generateUuid()],
                'isDisplay' => false,
            ])
            ->assertStatus(404);

        $record = $this->app->make(EventRepository::class)->find(new EventId($existing));
        $this->assertTrue($record?->isDisplay);
    }

    #[Test]
    public function generalUserCannotBulkUpdate(): void
    {
        $id = $this->generateUuid();
        $this->storeEvents($this->createEvent($id, tags: []));

        $this
            ->withGeneralAuth()
            ->postJson(route(EventRouteMap::BulkUpdate), ['eventIds' => [$id], 'isDisplay' => false])
            ->assertStatus(403);
    }
}
