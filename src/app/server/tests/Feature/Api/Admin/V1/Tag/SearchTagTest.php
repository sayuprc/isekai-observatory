<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Tag;

use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\SongType;
use Tag\Route\TagRouteMap;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class SearchTagTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;
    use WithAuth;

    #[Test]
    public function returnUnauthorizedWhenUnauthenticated(): void
    {
        $this->get(route(TagRouteMap::Search))
            ->assertStatus(401);
    }

    #[Test]
    public function searchAll(): void
    {
        $tagId = $this->generateUuid();

        $this->storeTags(
            $this->createTag($tagId, 'テストタグA', 1),
        );

        $this->withAuth()
            ->get(route(TagRouteMap::Search))
            ->assertStatus(200)
            ->assertExactJson([
                'tags' => [
                    [
                        'tagId' => $tagId,
                        'name' => 'テストタグA',
                        'orderNo' => 1,
                        'songCount' => 0,
                    ],
                ],
                'maxPage' => 1,
            ]);
    }

    #[Test]
    public function searchByName(): void
    {
        $matchedId = $this->generateUuid();
        $otherId = $this->generateUuid();

        $this->storeTags(
            $this->createTag($matchedId, 'テストタグA', 1),
            $this->createTag($otherId, 'テストタグB', 2),
        );

        $this->withAuth()
            ->get(route(TagRouteMap::Search, ['name' => 'テストタグA']))
            ->assertStatus(200)
            ->assertExactJson([
                'tags' => [
                    [
                        'tagId' => $matchedId,
                        'name' => 'テストタグA',
                        'orderNo' => 1,
                        'songCount' => 0,
                    ],
                ],
                'maxPage' => 1,
            ]);
    }

    #[Test]
    public function searchByNameNotFound(): void
    {
        $tagId = $this->generateUuid();

        $this->storeTags(
            $this->createTag($tagId, 'テストタグA', 1),
        );

        $this->withAuth()
            ->get(route(TagRouteMap::Search, ['name' => '存在しない']))
            ->assertStatus(200)
            ->assertExactJson([
                'tags' => [],
                'maxPage' => 0,
            ]);
    }

    #[Test]
    public function searchWithSortAndPaging(): void
    {
        $ids = [];
        $tags = [];

        for ($i = 1; $i <= 26; $i++) {
            $id = $this->generateUuid();
            $name = sprintf('tag-%02d', $i);
            $ids[$name] = $id;
            $tags[] = $this->createTag($id, $name, $i);
        }

        $this->storeTags(...$tags);

        $this->withAuth()
            ->get(route(TagRouteMap::Search, [
                'sort' => 'name',
                'order' => 'desc',
                'page' => 2,
                'per_page' => 25,
            ]))
            ->assertStatus(200)
            ->assertExactJson([
                'tags' => [
                    [
                        'tagId' => $ids['tag-01'],
                        'name' => 'tag-01',
                        'orderNo' => 1,
                        'songCount' => 0,
                    ],
                ],
                'maxPage' => 2,
            ]);
    }

    #[Test]
    public function returnsSongCountTaggedWithEachTag(): void
    {
        $tagId = $this->generateUuid();
        $this->storeTags($this->createTag($tagId, 'タグ', 1));
        $this->storeSongs(
            $this->createSong($this->generateUuid(), '楽曲1', '説明', SongType::Original, true, 1, [['tagId' => $tagId]]),
            $this->createSong($this->generateUuid(), '楽曲2', '説明', SongType::Original, true, 2, [['tagId' => $tagId]]),
        );

        $this->withAuth()
            ->get(route(TagRouteMap::Search))
            ->assertStatus(200)
            ->assertJsonPath('tags.0.songCount', 2);
    }
}
