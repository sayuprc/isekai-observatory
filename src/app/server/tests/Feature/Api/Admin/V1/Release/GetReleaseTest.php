<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Release;

use PHPUnit\Framework\Attributes\Test;
use Release\Domain\Models\ReleaseFormat;
use Release\Domain\Models\ReleaseGroupType;
use Release\Route\ReleaseRouteMap;
use Song\Domain\Models\SongType;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class GetReleaseTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;
    use WithAuth;

    #[Test]
    public function found(): void
    {
        $songId = $this->generateUuid();
        $releaseGroupId = $this->generateUuid();
        $releaseId = $this->generateUuid();

        $this->storeSongs(
            $this->createSong($songId, 'テスト楽曲1', '説明', SongType::Original, true, 10),
        );
        $this->storeReleaseGroups(
            $this->createReleaseGroup($releaseGroupId, '観測された春', ReleaseGroupType::Album, true),
        );
        $this->storeReleases(
            $this->createRelease(
                $releaseId,
                $releaseGroupId,
                '初回限定盤',
                true,
                color: '#4a5a78',
                orderNo: 10,
                formats: [ReleaseFormat::Cd->value, ReleaseFormat::Digital->value],
                media: [
                    [
                        'position' => 1,
                        'name' => 'CD1',
                        'tracks' => [
                            ['songId' => $songId, 'title' => null, 'trackNo' => 1],
                            ['songId' => null, 'title' => '管理対象外の楽曲', 'trackNo' => 2],
                            ['songId' => $songId, 'title' => 'テスト楽曲1 -instrumental-', 'trackNo' => 3],
                        ],
                    ],
                ],
            ),
        );

        $this->withAuth()
            ->getJson(route(ReleaseRouteMap::Get, $releaseId))
            ->assertStatus(200)
            ->assertExactJson([
                'release' => [
                    'releaseId' => $releaseId,
                    'releaseGroupId' => $releaseGroupId,
                    'name' => '初回限定盤',
                    'releasedOn' => '2024-01-01',
                    'description' => 'テスト用リリース',
                    'color' => '#4a5a78',
                    'isDisplay' => true,
                    'orderNo' => 10,
                    // 提供形態は値順で返る (配信=1, CD=2)
                    'formatValues' => [ReleaseFormat::Digital->value, ReleaseFormat::Cd->value],
                    'media' => [
                        [
                            'position' => 1,
                            'name' => 'CD1',
                            'tracks' => [
                                [
                                    'songId' => $songId,
                                    'title' => null,
                                    'trackNo' => 1,
                                ],
                                [
                                    'songId' => null,
                                    'title' => '管理対象外の楽曲',
                                    'trackNo' => 2,
                                ],
                                // 上書き名を持つ参照トラックは title に上書き名の生値が載る
                                [
                                    'songId' => $songId,
                                    'title' => 'テスト楽曲1 -instrumental-',
                                    'trackNo' => 3,
                                ],
                            ],
                        ],
                    ],
                ],
                'releaseGroupTitle' => '観測された春',
                'songs' => [
                    [
                        'mediumPosition' => 1,
                        'trackNo' => 1,
                        'songId' => $songId,
                        'title' => 'テスト楽曲1',
                    ],
                    // タイトルのみトラックも収録曲 read model に songId: null で載る
                    [
                        'mediumPosition' => 1,
                        'trackNo' => 2,
                        'songId' => null,
                        'title' => '管理対象外の楽曲',
                    ],
                    // 上書き名を持つ参照トラックでも read model は楽曲の正式名を返す
                    [
                        'mediumPosition' => 1,
                        'trackNo' => 3,
                        'songId' => $songId,
                        'title' => 'テスト楽曲1',
                    ],
                ],
            ]);
    }

    #[Test]
    public function notFound(): void
    {
        $this->withAuth()
            ->getJson(route(ReleaseRouteMap::Get, $this->generateUuid()))
            ->assertStatus(404);
    }

    #[Test]
    public function invalidId(): void
    {
        $this->withAuth()
            ->getJson('/api/admin/v1/releases/invalid-id')
            ->assertStatus(404);
    }
}
