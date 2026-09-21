<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Song;

use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\SongType;
use Song\Route\SongRouteMap;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class SearchSongTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;
    use WithAuth;

    #[Test]
    public function searchAll(): void
    {
        $uuid = $this->generateUuid();

        $this->storeSongs(
            $this->createSong($uuid, 'テスト楽曲', 'テスト楽曲説明', SongType::Original, true, 1, [], [], [], []),
        );

        $this->withAuth()
            ->get(route(SongRouteMap::Search))
            ->assertStatus(200)
            ->assertExactJson([
                'songs' => [
                    [
                        'songId' => $uuid,
                        'title' => 'テスト楽曲',
                        'type' => [
                            'name' => 'オリジナル曲',
                            'value' => 1,
                        ],
                        'isDisplay' => true,
                        'orderNo' => 1,
                    ],
                ],
                'maxPage' => 1,
            ]);
    }

    #[Test]
    public function searchByTitle(): void
    {
        $uuid1 = $this->generateUuid();
        $uuid2 = $this->generateUuid();

        $this->storeSongs(
            $this->createSong($uuid1, 'テスト楽曲', 'テスト楽曲説明', SongType::Original, true, 1, [], [], [], []),
            $this->createSong($uuid2, '比較テスト楽曲B', '比較テスト楽曲B説明', SongType::Cover, true, 2, [], [], [], []),
        );

        // 部分一致なので '比較テスト楽曲B' もヒットする
        $this->withAuth()
            ->get(route(SongRouteMap::Search, ['title' => 'テスト楽曲']))
            ->assertStatus(200)
            ->assertExactJson([
                'songs' => [
                    [
                        'songId' => $uuid1,
                        'title' => 'テスト楽曲',
                        'type' => [
                            'name' => 'オリジナル曲',
                            'value' => 1,
                        ],
                        'isDisplay' => true,
                        'orderNo' => 1,
                    ],
                    [
                        'songId' => $uuid2,
                        'title' => '比較テスト楽曲B',
                        'type' => [
                            'name' => 'カバー曲',
                            'value' => 2,
                        ],
                        'isDisplay' => true,
                        'orderNo' => 2,
                    ],
                ],
                'maxPage' => 1,
            ]);
    }

    #[Test]
    public function searchByType(): void
    {
        $uuid1 = $this->generateUuid();
        $uuid2 = $this->generateUuid();

        $this->storeSongs(
            $this->createSong($uuid1, 'テスト楽曲', 'テスト楽曲説明', SongType::Original, true, 1, [], [], [], []),
            $this->createSong($uuid2, '比較テスト楽曲B', '比較テスト楽曲B説明', SongType::Cover, true, 2, [], [], [], []),
        );

        $this->withAuth()
            ->get(route(SongRouteMap::Search, ['type' => SongType::Original->value]))
            ->assertStatus(200)
            ->assertExactJson([
                'songs' => [
                    [
                        'songId' => $uuid1,
                        'title' => 'テスト楽曲',
                        'type' => [
                            'name' => 'オリジナル曲',
                            'value' => 1,
                        ],
                        'isDisplay' => true,
                        'orderNo' => 1,
                    ],
                ],
                'maxPage' => 1,
            ]);
    }

    #[Test]
    public function searchByIsDisplay(): void
    {
        $displaySongId = $this->generateUuid();
        $hiddenSongId = $this->generateUuid();

        $this->storeSongs(
            $this->createSong($displaySongId, 'テスト楽曲', 'テスト楽曲説明', SongType::Original, true, 1, [], [], [], []),
            $this->createSong($hiddenSongId, '比較テスト楽曲B', '比較テスト楽曲B説明', SongType::Cover, false, 2, [], [], [], []),
        );

        $this->withAuth()
            ->get(route(SongRouteMap::Search, ['is_display' => false]))
            ->assertStatus(200)
            ->assertExactJson([
                'songs' => [
                    [
                        'songId' => $hiddenSongId,
                        'title' => '比較テスト楽曲B',
                        'type' => [
                            'name' => 'カバー曲',
                            'value' => 2,
                        ],
                        'isDisplay' => false,
                        'orderNo' => 2,
                    ],
                ],
                'maxPage' => 1,
            ]);
    }

    #[Test]
    public function searchByTitleNotFound(): void
    {
        $uuid = $this->generateUuid();

        $this->storeSongs(
            $this->createSong($uuid, 'テスト楽曲', 'テスト楽曲説明', SongType::Original, true, 1, [], [], [], []),
        );

        $this->withAuth()
            ->get(route(SongRouteMap::Search, ['title' => '存在しないタイトル']))
            ->assertStatus(200)
            ->assertExactJson([
                'songs' => [],
                'maxPage' => 0,
            ]);
    }
}
