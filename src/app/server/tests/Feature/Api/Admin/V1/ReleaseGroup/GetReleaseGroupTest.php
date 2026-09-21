<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\ReleaseGroup;

use DateType\ImmutableDate;
use PHPUnit\Framework\Attributes\Test;
use Release\Domain\Models\ReleaseFormat;
use Release\Domain\Models\ReleaseGroupType;
use Release\Route\ReleaseGroupRouteMap;
use Song\Domain\Models\SongType;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class GetReleaseGroupTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;
    use WithAuth;

    #[Test]
    public function found(): void
    {
        $songId = $this->generateUuid();
        $releaseGroupId = $this->generateUuid();
        $releaseId1 = $this->generateUuid();
        $releaseId2 = $this->generateUuid();

        $this->storeSongs(
            $this->createSong($songId, 'テスト楽曲1', '説明', SongType::Original, true, 10),
        );
        $this->storeReleaseGroups(
            $this->createReleaseGroup($releaseGroupId, '観測された春', ReleaseGroupType::Album, true, '1st アルバム', orderNo: 5),
        );
        $this->storeReleases(
            $this->createRelease(
                $releaseId1,
                $releaseGroupId,
                '配信',
                true,
                new ImmutableDate('2026-05-01'),
                formats: [ReleaseFormat::Digital->value],
                orderNo: 20,
                media: [
                    [
                        'position' => 1,
                        'name' => null,
                        'tracks' => [['songId' => $songId, 'title' => null, 'trackNo' => 1]],
                    ],
                ],
            ),
            $this->createRelease(
                $releaseId2,
                $releaseGroupId,
                '初回限定盤',
                true,
                new ImmutableDate('2026-06-01'),
                color: '#4a5a78',
                formats: [ReleaseFormat::Cd->value, ReleaseFormat::Dvd->value],
                orderNo: 10,
                media: [
                    [
                        'position' => 1,
                        'name' => 'CD',
                        'tracks' => [['songId' => $songId, 'title' => null, 'trackNo' => 1]],
                    ],
                    [
                        'position' => 2,
                        'name' => 'DVD',
                        'tracks' => [],
                    ],
                ],
            ),
        );

        $this->withAuth()
            ->getJson(route(ReleaseGroupRouteMap::Get, $releaseGroupId))
            ->assertStatus(200)
            ->assertExactJson([
                'releaseGroup' => [
                    'releaseGroupId' => $releaseGroupId,
                    'title' => '観測された春',
                    'typeValue' => ReleaseGroupType::Album->value,
                    'description' => '1st アルバム',
                    'isDisplay' => true,
                    'orderNo' => 5,
                ],
                'releases' => [
                    [
                        'releaseId' => $releaseId1,
                        'name' => '配信',
                        'releasedOn' => '2026-05-01',
                        'color' => '#989899',
                        'isDisplay' => true,
                        'orderNo' => 20,
                        'formatValues' => [ReleaseFormat::Digital->value],
                    ],
                    [
                        'releaseId' => $releaseId2,
                        'name' => '初回限定盤',
                        'releasedOn' => '2026-06-01',
                        'color' => '#4a5a78',
                        'isDisplay' => true,
                        'orderNo' => 10,
                        'formatValues' => [ReleaseFormat::Cd->value, ReleaseFormat::Dvd->value],
                    ],
                ],
            ]);
    }

    #[Test]
    public function notFound(): void
    {
        $this->withAuth()
            ->getJson(route(ReleaseGroupRouteMap::Get, $this->generateUuid()))
            ->assertStatus(404);
    }

    #[Test]
    public function invalidId(): void
    {
        $this->withAuth()
            ->getJson('/api/admin/v1/release-groups/invalid-id')
            ->assertStatus(404);
    }
}
