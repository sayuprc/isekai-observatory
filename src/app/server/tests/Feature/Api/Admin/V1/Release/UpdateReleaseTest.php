<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Release;

use Illuminate\Support\Facades\DB;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\Test;
use Release\Domain\Models\ReleaseFormat;
use Release\Domain\Models\ReleaseGroupType;
use Release\Route\ReleaseRouteMap;
use Song\Domain\Models\SongType;
use Support\Contracts\Uuid\UuidConverterInterface;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class UpdateReleaseTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;
    use WithAuth;

    #[Test]
    public function canUpdate(): void
    {
        $songId1 = $this->generateUuid();
        $songId2 = $this->generateUuid();
        $songId3 = $this->generateUuid();
        $releaseGroupId = $this->generateUuid();
        $releaseId = $this->generateUuid();

        $converter = $this->app->make(UuidConverterInterface::class);

        $this->storeSongs(
            $this->createSong($songId1, 'テスト楽曲1', '説明', SongType::Original, true, 10),
            $this->createSong($songId2, 'テスト楽曲2', '説明', SongType::Original, true, 20),
            $this->createSong($songId3, 'テスト楽曲3', '説明', SongType::Cover, false, 30),
        );
        $this->storeReleaseGroups(
            $this->createReleaseGroup($releaseGroupId, '観測された春', ReleaseGroupType::Album, true),
        );
        $this->storeReleases(
            $this->createRelease(
                $releaseId,
                $releaseGroupId,
                '旧版名',
                true,
                media: [
                    [
                        'position' => 1,
                        'name' => null,
                        'tracks' => [
                            ['songId' => $songId1, 'title' => null, 'trackNo' => 1],
                            ['songId' => $songId2, 'title' => null, 'trackNo' => 2],
                        ],
                    ],
                ],
            ),
        );

        $this->withAuth()
            ->putJson(route(ReleaseRouteMap::Update, $releaseId), [
                'name' => '新版名',
                'releasedOn' => '2026-05-09',
                'description' => '更新後の説明',
                'color' => '#2e62a0',
                'isDisplay' => false,
                'orderNo' => 20,
                'formatValues' => [ReleaseFormat::Cd->value],
                'media' => [
                    [
                        'position' => 1,
                        'name' => null,
                        'tracks' => [
                            ['songId' => $songId3, 'title' => null, 'trackNo' => 1],
                            ['songId' => $songId1, 'title' => null, 'trackNo' => 2],
                        ],
                    ],
                ],
            ])->assertStatus(200)
            ->assertJson(
                static fn (AssertableJson $json) => $json
                    ->has(
                        'release',
                        static fn (AssertableJson $json) => $json
                            ->where('releaseId', $releaseId)
                            ->where('releaseGroupId', $releaseGroupId)
                            ->where('name', '新版名')
                            ->where('releasedOn', '2026-05-09')
                            ->where('description', '更新後の説明')
                            ->where('color', '#2e62a0')
                            ->where('isDisplay', false)
                            ->where('orderNo', 20)
                            ->where('formatValues', [ReleaseFormat::Cd->value])
                            ->where('media.0.position', 1)
                            ->where('media.0.name', null)
                            ->where('media.0.tracks.0.songId', $songId3)
                            ->where('media.0.tracks.0.trackNo', 1)
                            ->where('media.0.tracks.1.songId', $songId1)
                            ->where('media.0.tracks.1.trackNo', 2),
                    ),
            );

        $tracks = DB::table('release_tracks')
            ->where('release_id', $converter->toBin($releaseId))
            ->orderBy('track_no')
            ->get()
            ->all();

        $this->assertCount(2, $tracks);
        $this->assertSame($songId3, $this->toUuid($tracks[0]->song_id));
        $this->assertSame($songId1, $this->toUuid($tracks[1]->song_id));
    }

    #[Test]
    public function canUpdateWithTitleOnlyTrack(): void
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
                '旧版名',
                true,
                media: [
                    [
                        'position' => 1,
                        'name' => null,
                        'tracks' => [['songId' => $songId, 'title' => null, 'trackNo' => 1]],
                    ],
                ],
            ),
        );

        $this->withAuth()
            ->putJson(route(ReleaseRouteMap::Update, $releaseId), [
                'name' => '新版名',
                'releasedOn' => '2026-05-09',
                'description' => '説明',
                'color' => '#989899',
                'isDisplay' => true,
                'orderNo' => 1,
                'formatValues' => [ReleaseFormat::Cd->value],
                'media' => [
                    [
                        'position' => 1,
                        'name' => null,
                        'tracks' => [
                            ['songId' => $songId, 'title' => null, 'trackNo' => 1],
                            ['songId' => null, 'title' => '管理対象外の楽曲', 'trackNo' => 2],
                        ],
                    ],
                ],
            ])->assertStatus(200)
            ->assertJson(
                static fn (AssertableJson $json) => $json
                    ->has(
                        'release',
                        static fn (AssertableJson $json) => $json
                            ->where('media.0.tracks.1.songId', null)
                            ->where('media.0.tracks.1.title', '管理対象外の楽曲')
                            ->where('media.0.tracks.1.trackNo', 2)
                            ->etc(),
                    ),
            );

        $this->assertDatabaseHas('release_tracks', [
            'track_no' => 2,
            'song_id' => null,
            'title' => '管理対象外の楽曲',
        ]);
    }

    #[Test]
    public function canUpdateWithOverriddenTrackTitle(): void
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
                '旧版名',
                true,
                media: [
                    [
                        'position' => 1,
                        'name' => null,
                        'tracks' => [['songId' => $songId, 'title' => null, 'trackNo' => 1]],
                    ],
                ],
            ),
        );

        // 既存の参照トラックに表示名の上書きを設定するケース
        $this->withAuth()
            ->putJson(route(ReleaseRouteMap::Update, $releaseId), [
                'name' => '新版名',
                'releasedOn' => '2026-05-09',
                'description' => '説明',
                'color' => '#989899',
                'isDisplay' => true,
                'orderNo' => 1,
                'formatValues' => [ReleaseFormat::Cd->value],
                'media' => [
                    [
                        'position' => 1,
                        'name' => null,
                        'tracks' => [
                            ['songId' => $songId, 'title' => 'テスト楽曲1 -instrumental-', 'trackNo' => 1],
                        ],
                    ],
                ],
            ])->assertStatus(200)
            ->assertJson(
                static fn (AssertableJson $json) => $json
                    ->has(
                        'release',
                        static fn (AssertableJson $json) => $json
                            ->where('media.0.tracks.0.songId', $songId)
                            ->where('media.0.tracks.0.title', 'テスト楽曲1 -instrumental-')
                            ->where('media.0.tracks.0.trackNo', 1)
                            ->etc(),
                    ),
            );

        $this->assertDatabaseHas('release_tracks', [
            'track_no' => 1,
            'title' => 'テスト楽曲1 -instrumental-',
        ]);
    }

    #[Test]
    public function updateFailsWhenFormatValuesIsEmpty(): void
    {
        $releaseGroupId = $this->generateUuid();
        $releaseId = $this->generateUuid();

        $this->storeReleaseGroups(
            $this->createReleaseGroup($releaseGroupId, '観測された春', ReleaseGroupType::Album, true),
        );
        $this->storeReleases(
            $this->createRelease($releaseId, $releaseGroupId, '旧版名', true),
        );

        $this->withAuth()
            ->putJson(route(ReleaseRouteMap::Update, $releaseId), [
                'name' => '新版名',
                'releasedOn' => '2026-05-09',
                'description' => '説明',
                'color' => '#989899',
                'isDisplay' => true,
                'orderNo' => 1,
                'formatValues' => [],
                'media' => [],
            ])->assertStatus(422);
    }

    #[Test]
    public function notFound(): void
    {
        $this->withAuth()
            ->putJson(route(ReleaseRouteMap::Update, $this->generateUuid()), [
                'name' => '新版名',
                'releasedOn' => '2026-05-09',
                'description' => '説明',
                'color' => '#989899',
                'isDisplay' => true,
                'orderNo' => 1,
                'formatValues' => [ReleaseFormat::Cd->value],
                'media' => [],
            ])->assertStatus(404);
    }

    #[Test]
    public function invalidId(): void
    {
        $this->withAuth()
            ->putJson('/api/admin/v1/releases/invalid-id', [
                'name' => '新版名',
                'releasedOn' => '2026-05-09',
                'description' => '説明',
                'color' => '#989899',
                'isDisplay' => true,
                'orderNo' => 1,
                'formatValues' => [ReleaseFormat::Cd->value],
                'media' => [],
            ])->assertStatus(404);
    }

    #[Test]
    public function forbidden(): void
    {
        $releaseGroupId = $this->generateUuid();
        $releaseId = $this->generateUuid();

        $this->storeReleaseGroups(
            $this->createReleaseGroup($releaseGroupId, '観測された春', ReleaseGroupType::Album, true),
        );
        $this->storeReleases(
            $this->createRelease($releaseId, $releaseGroupId, '旧版名', true),
        );

        $this->withGeneralAuth()
            ->putJson(route(ReleaseRouteMap::Update, $releaseId), [
                'name' => '新版名',
                'releasedOn' => '2026-05-09',
                'description' => '説明',
                'color' => '#989899',
                'isDisplay' => true,
                'orderNo' => 1,
                'formatValues' => [ReleaseFormat::Cd->value],
                'media' => [],
            ])->assertStatus(403);
    }

    #[Test]
    public function canUpdateWithSameSongAcrossMedia(): void
    {
        $songId = $this->generateUuid();
        $releaseGroupId = $this->generateUuid();
        $releaseId = $this->generateUuid();

        $converter = $this->app->make(UuidConverterInterface::class);

        $this->storeSongs($this->createSong($songId, 'テスト楽曲1', '説明', SongType::Original, true, 10));
        $this->storeReleaseGroups(
            $this->createReleaseGroup($releaseGroupId, '観測された春', ReleaseGroupType::Album, true),
        );
        $this->storeReleases(
            $this->createRelease($releaseId, $releaseGroupId, '旧版名', true),
        );

        $this->withAuth()
            ->putJson(route(ReleaseRouteMap::Update, $releaseId), [
                'name' => '新版名',
                'releasedOn' => '2026-05-09',
                'description' => '説明',
                'color' => '#989899',
                'isDisplay' => true,
                'orderNo' => 1,
                'formatValues' => [ReleaseFormat::Cd->value],
                'media' => [
                    [
                        'position' => 1,
                        'name' => null,
                        'tracks' => [['songId' => $songId, 'title' => null, 'trackNo' => 1]],
                    ],
                    [
                        'position' => 2,
                        'name' => null,
                        'tracks' => [['songId' => $songId, 'title' => null, 'trackNo' => 1]],
                    ],
                ],
            ])->assertStatus(200)
            ->assertJson(
                static fn (AssertableJson $json) => $json
                    ->has(
                        'release',
                        static fn (AssertableJson $json) => $json
                            ->where('media.0.tracks.0.songId', $songId)
                            ->where('media.1.tracks.0.songId', $songId)
                            ->etc(),
                    ),
            );

        $tracks = DB::table('release_tracks')
            ->where('release_id', $converter->toBin($releaseId))
            ->orderBy('position')
            ->get()
            ->all();

        $this->assertCount(2, $tracks);
        $this->assertSame($songId, $this->toUuid($tracks[0]->song_id));
        $this->assertSame($songId, $this->toUuid($tracks[1]->song_id));
    }
}
