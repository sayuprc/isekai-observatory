<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Viewer\V1\Release;

use DateType\ImmutableDate;
use PHPUnit\Framework\Attributes\Test;
use Release\Domain\Models\ReleaseFormat;
use Release\Domain\Models\ReleaseGroupType;
use Release\Route\ViewerReleaseGroupRouteMap;
use Song\Domain\Models\SongType;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class ListReleaseGroupTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function showPublicReleaseGroups(): void
    {
        $visibleSongId = $this->generateUuid();
        $hiddenSongId = $this->generateUuid();
        $releaseGroupId = $this->generateUuid();
        $releaseId1 = $this->generateUuid();
        $releaseId2 = $this->generateUuid();
        $hiddenReleaseId = $this->generateUuid();
        $hiddenGroupId = $this->generateUuid();
        $emptyGroupId = $this->generateUuid();

        $this->storeSongs(
            $this->createSong($visibleSongId, '公開楽曲', '説明', SongType::Original, true, 10),
            $this->createSong($hiddenSongId, '非公開楽曲', '説明', SongType::Original, false, 20),
        );
        $this->storeReleaseGroups(
            $this->createReleaseGroup($releaseGroupId, '観測された春', ReleaseGroupType::Album, true, '1st アルバム'),
            $this->createReleaseGroup($hiddenGroupId, '非公開グループ', ReleaseGroupType::Single, false),
            $this->createReleaseGroup($emptyGroupId, '公開リリース無しグループ', ReleaseGroupType::Ep, true),
        );
        $this->storeReleases(
            $this->createRelease(
                $releaseId1,
                $releaseGroupId,
                '配信',
                true,
                new ImmutableDate('2026-05-01'),
                description: '先行配信',
                formats: [ReleaseFormat::Digital->value],
                orderNo: 20,
                media: [
                    [
                        'position' => 1,
                        'name' => null,
                        'tracks' => [
                            ['songId' => $visibleSongId, 'title' => null, 'trackNo' => 1],
                            ['songId' => $hiddenSongId, 'title' => null, 'trackNo' => 2],
                            ['songId' => null, 'title' => '管理対象外の楽曲', 'trackNo' => 3],
                            ['songId' => $visibleSongId, 'title' => '公開楽曲 -acoustic-', 'trackNo' => 4],
                        ],
                    ],
                ],
            ),
            $this->createRelease(
                $releaseId2,
                $releaseGroupId,
                '初回限定盤',
                true,
                new ImmutableDate('2026-06-01'),
                description: 'CD+DVD',
                color: '#4a5a78',
                formats: [ReleaseFormat::Cd->value, ReleaseFormat::Dvd->value],
                orderNo: 10,
                media: [
                    [
                        'position' => 1,
                        'name' => 'CD',
                        'tracks' => [['songId' => $visibleSongId, 'title' => null, 'trackNo' => 1]],
                    ],
                    [
                        'position' => 2,
                        'name' => 'DVD',
                        'tracks' => [],
                    ],
                ],
            ),
            // 非公開リリースはグループが公開でも一覧に出ない
            $this->createRelease($hiddenReleaseId, $releaseGroupId, '非公開盤', false, new ImmutableDate('2026-04-01')),
            // 非公開グループ・公開リリース無しグループは一覧に出ない
            $this->createRelease($this->generateUuid(), $hiddenGroupId, '通常盤', true),
            $this->createRelease($this->generateUuid(), $emptyGroupId, '非公開盤', false),
        );

        $this->get(route(ViewerReleaseGroupRouteMap::List))
            ->assertStatus(200)
            ->assertExactJson([
                'releaseGroups' => [
                    [
                        'releaseGroupId' => $releaseGroupId,
                        'title' => '観測された春',
                        'type' => [
                            'name' => 'アルバム',
                            'value' => 2,
                        ],
                        'description' => '1st アルバム',
                        'firstReleasedOn' => '2026-05-01',
                        'releases' => [
                            [
                                'releaseId' => $releaseId1,
                                'name' => '配信',
                                'releasedOn' => '2026-05-01',
                                'description' => '先行配信',
                                'color' => '#989899',
                                'orderNo' => 20,
                                'formats' => [
                                    [
                                        'name' => '配信',
                                        'value' => 1,
                                    ],
                                ],
                                'media' => [
                                    [
                                        'position' => 1,
                                        'name' => null,
                                        'tracks' => [
                                            [
                                                'trackNo' => 1,
                                                'songId' => $visibleSongId,
                                                'title' => '公開楽曲',
                                                'isDisplay' => true,
                                            ],
                                            [
                                                'trackNo' => 2,
                                                'songId' => $hiddenSongId,
                                                'title' => '非公開楽曲',
                                                'isDisplay' => false,
                                            ],
                                            // タイトルのみトラックは songId: null / isDisplay: false で返る
                                            [
                                                'trackNo' => 3,
                                                'songId' => null,
                                                'title' => '管理対象外の楽曲',
                                                'isDisplay' => false,
                                            ],
                                            // 上書き名を持つ参照トラックは楽曲名ではなく上書き名で返る
                                            [
                                                'trackNo' => 4,
                                                'songId' => $visibleSongId,
                                                'title' => '公開楽曲 -acoustic-',
                                                'isDisplay' => true,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'releaseId' => $releaseId2,
                                'name' => '初回限定盤',
                                'releasedOn' => '2026-06-01',
                                'description' => 'CD+DVD',
                                'color' => '#4a5a78',
                                'orderNo' => 10,
                                'formats' => [
                                    [
                                        'name' => 'CD',
                                        'value' => 2,
                                    ],
                                    [
                                        'name' => 'DVD',
                                        'value' => 3,
                                    ],
                                ],
                                'media' => [
                                    [
                                        'position' => 1,
                                        'name' => 'CD',
                                        'tracks' => [
                                            [
                                                'trackNo' => 1,
                                                'songId' => $visibleSongId,
                                                'title' => '公開楽曲',
                                                'isDisplay' => true,
                                            ],
                                        ],
                                    ],
                                    [
                                        'position' => 2,
                                        'name' => 'DVD',
                                        'tracks' => [],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]);
    }

    #[Test]
    public function showsSameSongAsMultipleTracks(): void
    {
        $songId = $this->generateUuid();
        $releaseGroupId = $this->generateUuid();
        $releaseId = $this->generateUuid();

        $this->storeSongs(
            $this->createSong($songId, '公開楽曲', '説明', SongType::Original, true, 10),
        );
        $this->storeReleaseGroups(
            $this->createReleaseGroup($releaseGroupId, 'ライブ盤グループ', ReleaseGroupType::Album, true),
        );
        $this->storeReleases(
            $this->createRelease(
                $releaseId,
                $releaseGroupId,
                'ライブ盤',
                true,
                new ImmutableDate('2026-05-01'),
                media: [
                    // アンコールなど、同じ楽曲が同一媒体に複数回収録されるケース
                    [
                        'position' => 1,
                        'name' => null,
                        'tracks' => [
                            ['songId' => $songId, 'title' => null, 'trackNo' => 1],
                            ['songId' => $songId, 'title' => null, 'trackNo' => 2],
                        ],
                    ],
                ],
            ),
        );

        $this->get(route(ViewerReleaseGroupRouteMap::List))
            ->assertStatus(200)
            ->assertJsonCount(2, 'releaseGroups.0.releases.0.media.0.tracks')
            ->assertJsonPath('releaseGroups.0.releases.0.media.0.tracks.0.songId', $songId)
            ->assertJsonPath('releaseGroups.0.releases.0.media.0.tracks.0.trackNo', 1)
            ->assertJsonPath('releaseGroups.0.releases.0.media.0.tracks.1.songId', $songId)
            ->assertJsonPath('releaseGroups.0.releases.0.media.0.tracks.1.trackNo', 2);
    }

    #[Test]
    public function sortsReleasesByOrderNoWhenReleasedOnIsSame(): void
    {
        $releaseGroupId = $this->generateUuid();
        $releaseId1 = $this->generateUuid();
        $releaseId2 = $this->generateUuid();

        $this->storeReleaseGroups(
            $this->createReleaseGroup($releaseGroupId, '同日リリースの作品', ReleaseGroupType::Album, true),
        );
        $this->storeReleases(
            $this->createRelease($releaseId2, $releaseGroupId, '同日で order_no が大きい版', true, new ImmutableDate('2026-01-01'), orderNo: 2),
            $this->createRelease($releaseId1, $releaseGroupId, '同日で order_no が小さい版', true, new ImmutableDate('2026-01-01'), orderNo: 1),
        );

        $this->get(route(ViewerReleaseGroupRouteMap::List))
            ->assertStatus(200)
            ->assertJsonPath('releaseGroups.0.releases.0.releaseId', $releaseId1)
            ->assertJsonPath('releaseGroups.0.releases.1.releaseId', $releaseId2);
    }

    #[Test]
    public function returnsEmptyNameWhenReleaseHasNoName(): void
    {
        $releaseGroupId = $this->generateUuid();
        $releaseId = $this->generateUuid();

        $this->storeReleaseGroups(
            $this->createReleaseGroup($releaseGroupId, '版名なし作品', ReleaseGroupType::Album, true),
        );
        $this->storeReleases(
            $this->createRelease($releaseId, $releaseGroupId, '', true, new ImmutableDate('2026-01-01')),
        );

        $this->get(route(ViewerReleaseGroupRouteMap::List))
            ->assertStatus(200)
            ->assertJsonPath('releaseGroups.0.releases.0.releaseId', $releaseId)
            ->assertJsonPath('releaseGroups.0.releases.0.name', '');
    }

    #[Test]
    public function paginatesWithCursor(): void
    {
        $releaseGroupId1 = $this->generateUuid();
        $releaseGroupId2 = $this->generateUuid();

        $this->storeReleaseGroups(
            $this->createReleaseGroup($releaseGroupId1, '古い作品', ReleaseGroupType::Single, true),
            $this->createReleaseGroup($releaseGroupId2, '新しい作品', ReleaseGroupType::Album, true),
        );
        $this->storeReleases(
            $this->createRelease($this->generateUuid(), $releaseGroupId1, '配信', true, new ImmutableDate('2026-01-01')),
            $this->createRelease($this->generateUuid(), $releaseGroupId2, '配信', true, new ImmutableDate('2026-02-01')),
        );

        // 最古発売日の降順なので新しい作品が先
        $response = $this->get(route(ViewerReleaseGroupRouteMap::List, ['limit' => 1]))
            ->assertStatus(200)
            ->assertJsonCount(1, 'releaseGroups')
            ->assertJsonPath('releaseGroups.0.releaseGroupId', $releaseGroupId2);

        $cursor = $response->json('nextCursor');
        $this->assertIsString($cursor);

        $this->get(route(ViewerReleaseGroupRouteMap::List, ['limit' => 1, 'cursor' => $cursor]))
            ->assertStatus(200)
            ->assertJsonCount(1, 'releaseGroups')
            ->assertJsonPath('releaseGroups.0.releaseGroupId', $releaseGroupId1)
            ->assertJsonMissingPath('nextCursor');
    }

    #[Test]
    public function sortsByOrderNoDescendingWhenFirstReleasedOnIsSameAndPaginates(): void
    {
        $releaseGroupId1 = $this->generateUuid();
        $releaseGroupId2 = $this->generateUuid();

        $this->storeReleaseGroups(
            $this->createReleaseGroup($releaseGroupId1, '同日で order_no が小さい作品', ReleaseGroupType::Single, true, orderNo: 1),
            $this->createReleaseGroup($releaseGroupId2, '同日で order_no が大きい作品', ReleaseGroupType::Album, true, orderNo: 2),
        );
        $this->storeReleases(
            $this->createRelease($this->generateUuid(), $releaseGroupId1, '配信', true, new ImmutableDate('2026-01-01')),
            $this->createRelease($this->generateUuid(), $releaseGroupId2, '配信', true, new ImmutableDate('2026-01-01')),
        );

        // 同じ発売日なら order_no の降順で制御する
        $response = $this->get(route(ViewerReleaseGroupRouteMap::List, ['limit' => 1]))
            ->assertStatus(200)
            ->assertJsonCount(1, 'releaseGroups')
            ->assertJsonPath('releaseGroups.0.releaseGroupId', $releaseGroupId2);

        $cursor = $response->json('nextCursor');
        $this->assertIsString($cursor);

        $this->get(route(ViewerReleaseGroupRouteMap::List, ['limit' => 1, 'cursor' => $cursor]))
            ->assertStatus(200)
            ->assertJsonCount(1, 'releaseGroups')
            ->assertJsonPath('releaseGroups.0.releaseGroupId', $releaseGroupId1)
            ->assertJsonMissingPath('nextCursor');
    }
}
