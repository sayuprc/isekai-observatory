<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\SongTag;

use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\SongType;
use Song\Route\Tag\SongTagRouteMap;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class SearchSongTagTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;
    use WithAuth;

    #[Test]
    public function returnUnauthorizedWhenUnauthenticated(): void
    {
        $this->get(route(SongTagRouteMap::Search))
            ->assertStatus(401);
    }

    #[Test]
    public function searchAll(): void
    {
        $songTagId = $this->generateUuid();

        $this->storeSongTags(
            $this->createSongTag($songTagId, 'テストタグA', 1),
        );

        $this->withAuth()
            ->get(route(SongTagRouteMap::Search))
            ->assertStatus(200)
            ->assertExactJson([
                'tags' => [
                    [
                        'songTagId' => $songTagId,
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

        $this->storeSongTags(
            $this->createSongTag($matchedId, 'テストタグA', 1),
            $this->createSongTag($otherId, 'テストタグB', 2),
        );

        $this->withAuth()
            ->get(route(SongTagRouteMap::Search, ['name' => 'テストタグA']))
            ->assertStatus(200)
            ->assertExactJson([
                'tags' => [
                    [
                        'songTagId' => $matchedId,
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
        $songTagId = $this->generateUuid();

        $this->storeSongTags(
            $this->createSongTag($songTagId, 'テストタグA', 1),
        );

        $this->withAuth()
            ->get(route(SongTagRouteMap::Search, ['name' => '存在しない']))
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
            $tags[] = $this->createSongTag($id, $name, $i);
        }

        $this->storeSongTags(...$tags);

        $this->withAuth()
            ->get(route(SongTagRouteMap::Search, [
                'sort' => 'name',
                'order' => 'desc',
                'page' => 2,
                'per_page' => 25,
            ]))
            ->assertStatus(200)
            ->assertExactJson([
                'tags' => [
                    [
                        'songTagId' => $ids['tag-01'],
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
        $songTagId = $this->generateUuid();
        $this->storeSongTags($this->createSongTag($songTagId, 'タグ', 1));
        $this->storeSongs(
            $this->createSong($this->generateUuid(), '楽曲1', '説明', SongType::Original, true, 1, [['songTagId' => $songTagId]]),
            $this->createSong($this->generateUuid(), '楽曲2', '説明', SongType::Original, true, 2, [['songTagId' => $songTagId]]),
        );

        $this->withAuth()
            ->get(route(SongTagRouteMap::Search))
            ->assertStatus(200)
            ->assertJsonPath('tags.0.songCount', 2);
    }
}
