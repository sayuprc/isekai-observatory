<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Viewer\V1\Song;

use DateTimeImmutable;
use Media\Domain\Models\MediaType;
use PHPUnit\Framework\Attributes\Test;
use Release\Domain\Models\ReleaseGroupType;
use Song\Domain\Models\Persons\SongPersonRole;
use Song\Domain\Models\SongType;
use Song\Route\ViewerSongRouteMap;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class ListSongTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function showPublicSongs(): void
    {
        $visibleSongId = $this->generateUuid();
        $secondSongId = $this->generateUuid();
        $hiddenSongId = $this->generateUuid();
        $visibleMediaId = $this->generateUuid();
        $secondVisibleMediaId = $this->generateUuid();
        $hiddenMediaId = $this->generateUuid();
        $visibleReleaseId = $this->generateUuid();
        $hiddenReleaseId = $this->generateUuid();
        $lyricistId = $this->generateUuid();
        $composerId = $this->generateUuid();
        $arrangerId = $this->generateUuid();

        $this->storePersons(
            $this->createPerson($lyricistId, 'テスト作詞者A', 1),
            $this->createPerson($composerId, 'テスト作曲者A', 2),
            $this->createPerson($arrangerId, 'テスト編曲者A', 3),
        );

        $this->storeMedia(
            $this->createMedia(
                $visibleMediaId,
                '公開 MV',
                'https://example.com/public',
                MediaType::Mv,
                true,
                new DateTimeImmutable('2024-03-01 12:00:00'),
            ),
            $this->createMedia(
                $secondVisibleMediaId,
                '公開記事',
                'https://example.com/article',
                MediaType::AudioVideo,
                true,
                new DateTimeImmutable('2024-05-01 18:30:00'),
            ),
            $this->createMedia(
                $hiddenMediaId,
                '非公開 MV',
                'https://example.com/private',
                MediaType::Mv,
                false,
                new DateTimeImmutable('2024-04-01 09:00:00'),
            ),
        );

        $this->storeSongs(
            $this->createSong(
                $visibleSongId,
                'テスト楽曲',
                'Viewer の一覧表示向けに集約されたテスト楽曲説明',
                SongType::Original,
                true,
                1,
                [],
                [
                    ['personId' => $lyricistId, 'role' => SongPersonRole::Lyricist->value, 'orderNo' => 1],
                    ['personId' => $composerId, 'role' => SongPersonRole::Composer->value, 'orderNo' => 2],
                    ['personId' => $arrangerId, 'role' => SongPersonRole::Arranger->value, 'orderNo' => 3],
                ],
                [],
                [],
                [],
                [
                    ['mediaId' => $secondVisibleMediaId, 'orderNo' => 2],
                    ['mediaId' => $visibleMediaId, 'orderNo' => 1],
                    ['mediaId' => $hiddenMediaId, 'orderNo' => 3],
                ],
            ),
            $this->createSong(
                $secondSongId,
                '海月のうた',
                '2 曲目',
                SongType::Cover,
                true,
                2,
                [],
                [],
                [],
                [],
                [],
                [
                    ['mediaId' => $visibleMediaId, 'orderNo' => 1],
                ],
            ),
            $this->createSong(
                $hiddenSongId,
                '比較テスト楽曲B',
                '非公開楽曲',
                SongType::Cover,
                false,
                2,
                [],
                [],
                [],
                [],
                [],
            ),
        );

        $visibleReleaseGroupId = $this->generateUuid();
        $hiddenReleaseGroupId = $this->generateUuid();

        $this->storeReleaseGroups(
            $this->createReleaseGroup($visibleReleaseGroupId, '公開リリース', ReleaseGroupType::Single, true),
            $this->createReleaseGroup($hiddenReleaseGroupId, '非公開リリース', ReleaseGroupType::Album, false),
        );
        $this->storeReleases(
            $this->createRelease(
                $visibleReleaseId,
                $visibleReleaseGroupId,
                '配信',
                true,
                description: '公開リリース',
                media: [
                    [
                        'position' => 1,
                        'name' => null,
                        'tracks' => [['songId' => $visibleSongId, 'title' => null, 'trackNo' => 1]],
                    ],
                ],
            ),
            $this->createRelease(
                $hiddenReleaseId,
                $hiddenReleaseGroupId,
                'CD',
                false,
                description: '非公開リリース',
                media: [
                    [
                        'position' => 1,
                        'name' => null,
                        'tracks' => [['songId' => $visibleSongId, 'title' => null, 'trackNo' => 1]],
                    ],
                ],
            ),
        );

        $response = $this->get(route(ViewerSongRouteMap::List, ['limit' => 1]))
            ->assertStatus(200)
            ->assertExactJson([
                'songs' => [
                    [
                        'songId' => $secondSongId,
                        'title' => '海月のうた',
                        'type' => [
                            'name' => 'カバー曲',
                            'value' => 2,
                        ],
                        'description' => '2 曲目',
                        'lyricists' => [],
                        'composers' => [],
                        'arrangers' => [],
                        'counts' => [
                            'releaseCount' => 0,
                            'mediaCount' => 1,
                        ],
                        'media' => [
                            [
                                'mediaId' => $visibleMediaId,
                                'title' => '公開 MV',
                                'type' => [
                                    'name' => 'MV',
                                    'value' => 1,
                                ],
                                'url' => 'https://example.com/public',
                                'publishedAt' => '2024-03-01T12:00:00+09:00',
                            ],
                        ],
                        'releaseGroups' => [],
                    ],
                ],
                'nextCursor' => base64_encode((string)json_encode([
                    'orderNo' => 2,
                    'songId' => $secondSongId,
                ], JSON_THROW_ON_ERROR)),
            ]);

        $cursor = $response->json('nextCursor');

        $this->assertIsString($cursor);

        $this->get(route(ViewerSongRouteMap::List, ['limit' => 1, 'cursor' => $cursor]))
            ->assertStatus(200)
            ->assertExactJson([
                'songs' => [
                    [
                        'songId' => $visibleSongId,
                        'title' => 'テスト楽曲',
                        'type' => [
                            'name' => 'オリジナル曲',
                            'value' => 1,
                        ],
                        'description' => 'Viewer の一覧表示向けに集約されたテスト楽曲説明',
                        'lyricists' => ['テスト作詞者A'],
                        'composers' => ['テスト作曲者A'],
                        'arrangers' => ['テスト編曲者A'],
                        'counts' => [
                            'releaseCount' => 1,
                            'mediaCount' => 2,
                        ],
                        'media' => [
                            [
                                'mediaId' => $visibleMediaId,
                                'title' => '公開 MV',
                                'type' => [
                                    'name' => 'MV',
                                    'value' => 1,
                                ],
                                'url' => 'https://example.com/public',
                                'publishedAt' => '2024-03-01T12:00:00+09:00',
                            ],
                            [
                                'mediaId' => $secondVisibleMediaId,
                                'title' => '公開記事',
                                'type' => [
                                    'name' => '音源動画',
                                    'value' => 2,
                                ],
                                'url' => 'https://example.com/article',
                                'publishedAt' => '2024-05-01T18:30:00+09:00',
                            ],
                        ],
                        'releaseGroups' => [
                            [
                                'releaseGroupId' => $visibleReleaseGroupId,
                                'title' => '公開リリース',
                                'type' => [
                                    'name' => 'シングル',
                                    'value' => 1,
                                ],
                                'firstReleasedOn' => '2024-01-01',
                                'color' => '#989899',
                            ],
                        ],
                    ],
                ],
            ]);
    }
}
