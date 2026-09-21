<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Release;

use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\Test;
use Release\Domain\Models\ReleaseFormat;
use Release\Domain\Models\ReleaseGroupType;
use Release\Route\ReleaseRouteMap;
use Song\Domain\Models\SongType;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class CreateReleaseTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;
    use WithAuth;

    #[Test]
    public function canCreate(): void
    {
        $songId = $this->generateUuid();
        $releaseGroupId = $this->generateUuid();

        $this->storeSongs(
            $this->createSong($songId, 'テスト楽曲1', '説明', SongType::Original, true, 1),
        );
        $this->storeReleaseGroups(
            $this->createReleaseGroup($releaseGroupId, '観測された春', ReleaseGroupType::Album, true),
        );

        $this->withAuth()
            ->postJson(route(ReleaseRouteMap::Create), [
                'releaseGroupId' => $releaseGroupId,
                'name' => '初回限定盤',
                'releasedOn' => '2026-05-09',
                'description' => '',
                'color' => '#4a5a78',
                'isDisplay' => true,
                'orderNo' => 10,
                'formatValues' => [ReleaseFormat::Cd->value, ReleaseFormat::Dvd->value],
                'media' => [
                    [
                        'position' => 1,
                        'name' => null,
                        'tracks' => [
                            ['songId' => $songId, 'title' => null, 'trackNo' => 1],
                        ],
                    ],
                    [
                        'position' => 2,
                        'name' => 'DVD',
                        'tracks' => [],
                    ],
                ],
            ])->assertStatus(200)
            ->assertJson(
                static fn (AssertableJson $json) => $json
                    ->has(
                        'release',
                        static fn (AssertableJson $json) => $json
                            ->whereType('releaseId', 'string')
                            ->where('releaseGroupId', $releaseGroupId)
                            ->where('name', '初回限定盤')
                            ->where('releasedOn', '2026-05-09')
                            ->where('description', '')
                            ->where('color', '#4a5a78')
                            ->where('isDisplay', true)
                            ->where('orderNo', 10)
                            ->where('formatValues', [ReleaseFormat::Cd->value, ReleaseFormat::Dvd->value])
                            ->where('media.0.position', 1)
                            ->where('media.0.name', null)
                            ->where('media.0.tracks.0.songId', $songId)
                            ->where('media.0.tracks.0.trackNo', 1)
                            ->where('media.1.position', 2)
                            ->where('media.1.name', 'DVD')
                            ->where('media.1.tracks', []),
                    ),
            );

        $this->assertDatabaseCount('release_formats', 2);
        $this->assertDatabaseCount('release_media', 2);
        $this->assertDatabaseCount('release_tracks', 1);
    }

    #[Test]
    public function canCreateWithTitleOnlyTrack(): void
    {
        $songId = $this->generateUuid();
        $releaseGroupId = $this->generateUuid();

        $this->storeSongs(
            $this->createSong($songId, 'テスト楽曲1', '説明', SongType::Original, true, 1),
        );
        $this->storeReleaseGroups(
            $this->createReleaseGroup($releaseGroupId, '観測された春', ReleaseGroupType::Album, true),
        );

        $this->withAuth()
            ->postJson(route(ReleaseRouteMap::Create), [
                'releaseGroupId' => $releaseGroupId,
                'name' => '初回限定盤',
                'releasedOn' => '2026-05-09',
                'description' => '',
                'color' => '#989899',
                'isDisplay' => true,
                'orderNo' => 10,
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
                            ->where('media.0.tracks.0.songId', $songId)
                            ->where('media.0.tracks.0.title', null)
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
    public function canCreateWithSameSongInSameMedium(): void
    {
        $songId = $this->generateUuid();
        $releaseGroupId = $this->generateUuid();

        $this->storeSongs(
            $this->createSong($songId, 'テスト楽曲1', '説明', SongType::Original, true, 1),
        );
        $this->storeReleaseGroups(
            $this->createReleaseGroup($releaseGroupId, '観測された春', ReleaseGroupType::Album, true),
        );

        // ライブ盤のアンコールなど、同じ楽曲が同一媒体に複数回収録されるケース
        $this->withAuth()
            ->postJson(route(ReleaseRouteMap::Create), [
                'releaseGroupId' => $releaseGroupId,
                'name' => 'ライブ盤',
                'releasedOn' => '2026-05-09',
                'description' => '',
                'color' => '#989899',
                'isDisplay' => true,
                'orderNo' => 10,
                'formatValues' => [ReleaseFormat::Cd->value],
                'media' => [
                    [
                        'position' => 1,
                        'name' => null,
                        'tracks' => [
                            ['songId' => $songId, 'title' => null, 'trackNo' => 1],
                            ['songId' => $songId, 'title' => null, 'trackNo' => 2],
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
                            ->where('media.0.tracks.0.trackNo', 1)
                            ->where('media.0.tracks.1.songId', $songId)
                            ->where('media.0.tracks.1.trackNo', 2)
                            ->etc(),
                    ),
            );

        $this->assertDatabaseCount('release_tracks', 2);
    }

    #[Test]
    public function canCreateWithOverriddenTrackTitle(): void
    {
        $songId = $this->generateUuid();
        $releaseGroupId = $this->generateUuid();

        $this->storeSongs(
            $this->createSong($songId, 'テスト楽曲1', '説明', SongType::Original, true, 1),
        );
        $this->storeReleaseGroups(
            $this->createReleaseGroup($releaseGroupId, '観測された春', ReleaseGroupType::Album, true),
        );

        // 楽曲への紐づきを維持したまま、トラックとしての表示名だけを上書きするケース
        $this->withAuth()
            ->postJson(route(ReleaseRouteMap::Create), [
                'releaseGroupId' => $releaseGroupId,
                'name' => '初回限定盤',
                'releasedOn' => '2026-05-09',
                'description' => '',
                'color' => '#989899',
                'isDisplay' => true,
                'orderNo' => 10,
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
    public function createFailsWhenTrackHasNeitherSongIdNorTitle(): void
    {
        $releaseGroupId = $this->generateUuid();

        $this->storeReleaseGroups(
            $this->createReleaseGroup($releaseGroupId, '観測された春', ReleaseGroupType::Album, true),
        );

        $this->withAuth()
            ->postJson(route(ReleaseRouteMap::Create), [
                'releaseGroupId' => $releaseGroupId,
                'name' => '初回限定盤',
                'releasedOn' => '2026-05-09',
                'description' => '',
                'color' => '#989899',
                'isDisplay' => true,
                'orderNo' => 10,
                'formatValues' => [ReleaseFormat::Cd->value],
                'media' => [
                    [
                        'position' => 1,
                        'name' => null,
                        'tracks' => [
                            ['songId' => null, 'title' => null, 'trackNo' => 1],
                        ],
                    ],
                ],
            ])->assertStatus(400)
            ->assertJson(
                static fn (AssertableJson $json) => $json
                    ->where('code', 'business_rule_violation')
                    ->where('message', '収録曲には楽曲かタイトルの少なくとも一方を指定してください。'),
            );
    }

    #[Test]
    public function canCreateWithEmptyName(): void
    {
        $releaseGroupId = $this->generateUuid();

        $this->storeReleaseGroups(
            $this->createReleaseGroup($releaseGroupId, '観測された春', ReleaseGroupType::Album, true),
        );

        $this->withAuth()
            ->postJson(route(ReleaseRouteMap::Create), [
                'releaseGroupId' => $releaseGroupId,
                'name' => '',
                'releasedOn' => '2026-05-09',
                'description' => '',
                'color' => '#989899',
                'isDisplay' => true,
                'orderNo' => 1,
                'formatValues' => [ReleaseFormat::Digital->value],
                'media' => [],
            ])->assertStatus(200)
            ->assertJson(
                static fn (AssertableJson $json) => $json
                    ->has(
                        'release',
                        static fn (AssertableJson $json) => $json
                            ->whereType('releaseId', 'string')
                            ->where('releaseGroupId', $releaseGroupId)
                            ->where('name', '')
                            ->where('releasedOn', '2026-05-09')
                            ->where('description', '')
                            ->where('color', '#989899')
                            ->where('isDisplay', true)
                            ->where('orderNo', 1)
                            ->where('formatValues', [ReleaseFormat::Digital->value])
                            ->where('media', []),
                    ),
            );

        $this->assertDatabaseHas('releases', [
            'name' => '',
            'is_display' => true,
            'order_no' => 1,
        ]);
    }

    #[Test]
    public function createFailsWhenFormatValuesIsEmpty(): void
    {
        $releaseGroupId = $this->generateUuid();

        $this->storeReleaseGroups(
            $this->createReleaseGroup($releaseGroupId, '観測された春', ReleaseGroupType::Album, true),
        );

        $this->withAuth()
            ->postJson(route(ReleaseRouteMap::Create), [
                'releaseGroupId' => $releaseGroupId,
                'name' => '通常盤',
                'releasedOn' => '2026-05-09',
                'description' => '',
                'color' => '#989899',
                'isDisplay' => true,
                'orderNo' => 1,
                'formatValues' => [],
                'media' => [],
            ])->assertStatus(422);
    }

    #[Test]
    public function createFailsWhenReleaseGroupDoesNotExist(): void
    {
        $this->withAuth()
            ->postJson(route(ReleaseRouteMap::Create), [
                'releaseGroupId' => $this->generateUuid(),
                'name' => '通常盤',
                'releasedOn' => '2026-05-09',
                'description' => '',
                'color' => '#989899',
                'isDisplay' => true,
                'orderNo' => 1,
                'formatValues' => [ReleaseFormat::Digital->value],
                'media' => [],
            ])->assertStatus(400)
            ->assertJson(['message' => '指定されたリリースグループが存在しません。']);
    }

    #[Test]
    public function createFailsWhenReleasedOnIsInvalid(): void
    {
        $this->withAuth()
            ->postJson(route(ReleaseRouteMap::Create), [
                'releaseGroupId' => $this->generateUuid(),
                'name' => '通常盤',
                'releasedOn' => 'invalid-date',
                'description' => '説明',
                'color' => '#989899',
                'isDisplay' => true,
                'orderNo' => 1,
                'formatValues' => [ReleaseFormat::Digital->value],
                'media' => [],
            ])->assertStatus(422)
            ->assertJson(
                static fn (AssertableJson $json) => $json
                    ->where('code', 'validation_failed')
                    ->whereType('message', 'string')
                    ->has(
                        'details',
                        1,
                        static fn (AssertableJson $json) => $json
                            ->where('field', 'releasedOn')
                            ->where('message', '日付は YYYY-MM-DD 形式で指定してください'),
                    ),
            );
    }
}
