<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Release;

use PHPUnit\Framework\Attributes\Test;
use Release\Domain\Models\ReleaseGroupType;
use Release\Domain\Models\ReleaseId;
use Release\Infrastructures\ReleaseRepository;
use Release\Route\ReleaseRouteMap;
use Song\Domain\Models\SongType;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class DeleteReleaseTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;
    use WithAuth;

    #[Test]
    public function canDelete(): void
    {
        $releaseGroupId = $this->generateUuid();
        $releaseId = $this->generateUuid();
        $repository = $this->app->make(ReleaseRepository::class);

        $this->storeReleaseGroups(
            $this->createReleaseGroup($releaseGroupId, '観測された春', ReleaseGroupType::Album, true),
        );

        $repository->save(
            $this->createRelease($releaseId, $releaseGroupId, '削除対象', true),
        );

        $this->withAuth()
            ->delete(route(ReleaseRouteMap::Delete, $releaseId))
            ->assertStatus(204);

        $this->assertNull($repository->find(new ReleaseId($releaseId)));
    }

    #[Test]
    public function canDeleteReleaseWithMediaAndTracks(): void
    {
        $songId = $this->generateUuid();
        $releaseGroupId = $this->generateUuid();
        $releaseId = $this->generateUuid();
        $repository = $this->app->make(ReleaseRepository::class);

        $this->storeSongs(
            $this->createSong($songId, 'テスト楽曲1', '説明', SongType::Original, true, 1),
        );
        $this->storeReleaseGroups(
            $this->createReleaseGroup($releaseGroupId, '観測された春', ReleaseGroupType::Album, true),
        );

        $repository->save(
            $this->createRelease(
                $releaseId,
                $releaseGroupId,
                '削除対象',
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
            ->delete(route(ReleaseRouteMap::Delete, $releaseId))
            ->assertStatus(204);

        $this->assertNull($repository->find(new ReleaseId($releaseId)));
        $this->assertDatabaseCount('release_media', 0);
        $this->assertDatabaseCount('release_tracks', 0);
    }

    #[Test]
    public function canDeleteEvenIfTargetDoesNotExist(): void
    {
        $this->withAuth()
            ->delete(route(ReleaseRouteMap::Delete, 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'))
            ->assertStatus(204);
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
            $this->createRelease($releaseId, $releaseGroupId, '削除対象', true),
        );

        $this->withGeneralAuth()
            ->delete(route(ReleaseRouteMap::Delete, $releaseId))
            ->assertStatus(403);
    }

    #[Test]
    public function invalidId(): void
    {
        $this->withAuth()
            ->delete('/api/admin/v1/releases/invalid-id')
            ->assertStatus(404);
    }
}
