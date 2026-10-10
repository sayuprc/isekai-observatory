<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Public\V1\ReleaseGroup;

use DateType\ImmutableDate;
use PHPUnit\Framework\Attributes\Test;
use Release\Domain\Models\ReleaseFormat;
use Release\Domain\Models\ReleaseGroupType;
use Release\Route\PublicReleaseGroupRouteMap;
use Song\Domain\Models\SongType;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class ListReleaseGroupTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function returnsPublicReleasesWithTracksReferencingPublicSongsOnly(): void
    {
        $releaseGroupId = $this->generateUuid();
        $releaseId = $this->generateUuid();
        $visibleSongId = $this->generateUuid();
        $hiddenSongId = $this->generateUuid();

        $this->storeSongs(
            $this->createSong($visibleSongId, '公開楽曲', '説明', SongType::Original, true, 1),
            $this->createSong($hiddenSongId, '非公開楽曲', '説明', SongType::Original, false, 2),
        );
        $this->storeReleaseGroups($this->createReleaseGroup($releaseGroupId, 'アルバム', ReleaseGroupType::Album, description: '説明'));
        $this->storeReleases(
            $this->createRelease(
                $releaseId,
                $releaseGroupId,
                '通常盤',
                true,
                new ImmutableDate('2026-05-01'),
                description: 'CD',
                color: '#4a5a78',
                formats: [ReleaseFormat::Cd->value],
                media: [[
                    'position' => 1,
                    'name' => 'Disc 1',
                    'tracks' => [
                        ['songId' => $visibleSongId, 'title' => null, 'trackNo' => 1],
                        ['songId' => $hiddenSongId, 'title' => null, 'trackNo' => 2],
                        ['songId' => null, 'title' => '管理対象外の楽曲', 'trackNo' => 3],
                    ],
                ]],
            ),
            $this->createRelease($this->generateUuid(), $releaseGroupId, '非公開盤', false, orderNo: 2),
        );

        $this->get(route(PublicReleaseGroupRouteMap::List))
            ->assertStatus(200)
            ->assertExactJson([
                'items' => [[
                    'releaseGroupId' => $releaseGroupId,
                    'title' => 'アルバム',
                    'type' => ReleaseGroupType::Album->value,
                    'description' => '説明',
                    'releases' => [[
                        'releaseId' => $releaseId,
                        'name' => '通常盤',
                        'releasedOn' => '2026-05-01',
                        'description' => 'CD',
                        'color' => '#4a5a78',
                        'formats' => [ReleaseFormat::Cd->value],
                        'media' => [[
                            'position' => 1,
                            'name' => 'Disc 1',
                            'tracks' => [
                                ['trackNo' => 1, 'songId' => $visibleSongId, 'title' => '公開楽曲'],
                                ['trackNo' => 2, 'songId' => null, 'title' => '非公開楽曲'],
                                ['trackNo' => 3, 'songId' => null, 'title' => '管理対象外の楽曲'],
                            ],
                        ]],
                    ]],
                ]],
            ]);
    }

    #[Test]
    public function excludesPrivateGroupAndGroupWithoutPublicRelease(): void
    {
        $privateGroupId = $this->generateUuid();
        $emptyGroupId = $this->generateUuid();
        $this->storeReleaseGroups(
            $this->createReleaseGroup($privateGroupId, '非公開の作品', ReleaseGroupType::Single, isDisplay: false),
            $this->createReleaseGroup($emptyGroupId, '公開リリースの無い作品', ReleaseGroupType::Single),
        );
        $this->storeReleases(
            $this->createRelease($this->generateUuid(), $privateGroupId, '通常盤', true),
            $this->createRelease($this->generateUuid(), $emptyGroupId, '非公開盤', false),
        );

        $this->get(route(PublicReleaseGroupRouteMap::List))
            ->assertStatus(200)
            ->assertExactJson(['items' => []]);
    }

    #[Test]
    public function paginatesInDescendingOrderNoWithPageToken(): void
    {
        $firstId = $this->generateUuid();
        $secondId = $this->generateUuid();
        $this->storeReleaseGroups(
            $this->createReleaseGroup($secondId, '作品 B', ReleaseGroupType::Single, orderNo: 1),
            $this->createReleaseGroup($firstId, '作品 A', ReleaseGroupType::Single, orderNo: 2),
        );
        $this->storeReleases(
            $this->createRelease($this->generateUuid(), $firstId, '通常盤', true),
            $this->createRelease($this->generateUuid(), $secondId, '通常盤', true),
        );

        $first = $this->get(route(PublicReleaseGroupRouteMap::List, ['pageSize' => 1]))
            ->assertStatus(200)
            ->assertJsonPath('items.0.releaseGroupId', $firstId);

        $this->get(route(PublicReleaseGroupRouteMap::List, ['pageSize' => 1, 'pageToken' => $first->json('nextPageToken')]))
            ->assertStatus(200)
            ->assertJsonPath('items.0.releaseGroupId', $secondId)
            ->assertJsonMissingPath('nextPageToken');
    }
}
