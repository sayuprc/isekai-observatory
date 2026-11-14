<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Song;

use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\SongId;
use Song\Domain\Models\SongType;
use Song\Infrastructures\SongRepository;
use Song\Route\SongRouteMap;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditTargetType;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\Concerns\AssertsAuditLog;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class BulkUpdateSongTest extends DatabaseTestCase
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
        $this->storeSongs(
            $this->createSong($first, '曲名', '説明', 'https://example.com/l', SongType::Original, true, 1, [[
                'tagId' => $removeTagId,
            ]]),
            $this->createSong($second, '曲名', '説明', 'https://example.com/l', SongType::Original, true, 1, []),
        );

        $this
            ->withAuth()
            ->postJson(route(SongRouteMap::BulkUpdate), [
                'songIds' => [$first, $second],
                'addTagIds' => [$addTagId],
                'removeTagIds' => [$removeTagId],
                'isDisplay' => false,
            ])
            ->assertOk()
            ->assertExactJson(['updatedCount' => 2]);

        $repository = $this->app->make(SongRepository::class);

        foreach ([$first, $second] as $id) {
            $record = $repository->find(new SongId($id));
            $this->assertFalse($record?->isDisplay);
            $this->assertSame([$addTagId], array_column($record?->tags->toArray() ?? [], 'tag_id'));
        }

        $this->assertAuditLogCount(2);
        $this->findAuditLog(AuditAction::Update, AuditTargetType::Song, $first);
        $this->findAuditLog(AuditAction::Update, AuditTargetType::Song, $second);
    }

    #[Test]
    public function nothingChangesWhenAnyRecordDoesNotExist(): void
    {
        $existing = $this->generateUuid();
        $this->storeSongs($this->createSong(
            $existing,
            '曲名',
            '説明',
            'https://example.com/l',
            SongType::Original,
            true,
            1,
            [],
        ));

        $this
            ->withAuth()
            ->postJson(route(SongRouteMap::BulkUpdate), [
                'songIds' => [$existing, $this->generateUuid()],
                'isDisplay' => false,
            ])
            ->assertNotFound();

        $record = $this->app->make(SongRepository::class)->find(new SongId($existing));
        $this->assertTrue($record?->isDisplay);
    }

    #[Test]
    public function generalUserCannotBulkUpdate(): void
    {
        $id = $this->generateUuid();
        $this->storeSongs($this->createSong(
            $id,
            '曲名',
            '説明',
            'https://example.com/l',
            SongType::Original,
            true,
            1,
            [],
        ));

        $this
            ->withGeneralAuth()
            ->postJson(route(SongRouteMap::BulkUpdate), ['songIds' => [$id], 'isDisplay' => false])
            ->assertForbidden();
    }
}
