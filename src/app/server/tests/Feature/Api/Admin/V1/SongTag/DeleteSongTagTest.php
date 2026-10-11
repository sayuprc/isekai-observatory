<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\SongTag;

use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\SongType;
use Song\Infrastructures\Tag\SongTagRepository;
use Song\Route\Tag\SongTagRouteMap;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class DeleteSongTagTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;
    use WithAuth;

    #[Test]
    public function canDelete(): void
    {
        $uuid = $this->generateUuid();

        $repository = $this->app->make(SongTagRepository::class);
        $repository->save($this->createSongTag($uuid, 'テストタグA', 1));

        $this->withAuth()
            ->delete(route(SongTagRouteMap::Delete, $uuid))
            ->assertStatus(204);

        $this->assertNull($repository->find($this->createSongTag($uuid, 'テストタグA', 1)->songTagId));
    }

    #[Test]
    public function cannotDeleteWhenUsedInSong(): void
    {
        $songTagId = $this->generateUuid();
        $songTag = $this->createSongTag($songTagId, 'テストタグA', 1);
        $repository = $this->app->make(SongTagRepository::class);

        $this->storeSongTags($songTag);
        $this->storeSongs($this->createSong(
            $this->generateUuid(),
            '曲名',
            '説明',
            SongType::Original,
            true,
            1,
            [['songTagId' => $songTagId, 'orderNo' => 1]],
            [],
            [],
            [],
        ));

        $this->withAuth()
            ->delete(route(SongTagRouteMap::Delete, $songTagId))
            ->assertStatus(400)
            ->assertExactJson([
                'code' => 'business_rule_violation',
                'message' => 'この楽曲タグは楽曲に使用されているため削除できません',
            ]);

        $this->assertNotNull($repository->find($songTag->songTagId));
    }

    #[Test]
    public function canDeleteEvenIfTargetDoesNotExist(): void
    {
        $this->withAuth()
            ->delete(route(SongTagRouteMap::Delete, 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'))
            ->assertStatus(204);
    }

    #[Test]
    public function requiresAuthentication(): void
    {
        $this->delete(route(SongTagRouteMap::Delete, 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'))
            ->assertStatus(401);
    }
}
