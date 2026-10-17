<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Tag;

use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\SongType;
use Tag\Infrastructures\TagRepository;
use Tag\Route\TagRouteMap;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class DeleteTagTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;
    use WithAuth;

    #[Test]
    public function canDelete(): void
    {
        $uuid = $this->generateUuid();

        $repository = $this->app->make(TagRepository::class);
        $repository->save($this->createTag($uuid, 'テストタグA', 1));

        $this->withAuth()
            ->delete(route(TagRouteMap::Delete, $uuid))
            ->assertStatus(204);

        $this->assertNull($repository->find($this->createTag($uuid, 'テストタグA', 1)->tagId));
    }

    #[Test]
    public function cannotDeleteWhenUsedInSong(): void
    {
        $tagId = $this->generateUuid();
        $tag = $this->createTag($tagId, 'テストタグA', 1);
        $repository = $this->app->make(TagRepository::class);

        $this->storeTags($tag);
        $this->storeSongs($this->createSong(
            $this->generateUuid(),
            '曲名',
            '説明',
            SongType::Original,
            true,
            1,
            [['tagId' => $tagId, 'orderNo' => 1]],
            [],
            [],
            [],
        ));

        $this->withAuth()
            ->delete(route(TagRouteMap::Delete, $tagId))
            ->assertStatus(400)
            ->assertExactJson([
                'code' => 'business_rule_violation',
                'message' => 'このタグはほかの記録に付いているため削除できません',
            ]);

        $this->assertNotNull($repository->find($tag->tagId));
    }

    #[Test]
    public function canDeleteEvenIfTargetDoesNotExist(): void
    {
        $this->withAuth()
            ->delete(route(TagRouteMap::Delete, 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'))
            ->assertStatus(204);
    }

    #[Test]
    public function requiresAuthentication(): void
    {
        $this->delete(route(TagRouteMap::Delete, 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'))
            ->assertStatus(401);
    }
}
