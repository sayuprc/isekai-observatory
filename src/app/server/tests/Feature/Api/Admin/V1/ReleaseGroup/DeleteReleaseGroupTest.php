<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\ReleaseGroup;

use PHPUnit\Framework\Attributes\Test;
use Release\Domain\Models\ReleaseGroupType;
use Release\Route\ReleaseGroupRouteMap;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class DeleteReleaseGroupTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;
    use WithAuth;

    #[Test]
    public function canDelete(): void
    {
        $releaseGroupId = $this->generateUuid();

        $this->storeReleaseGroups(
            $this->createReleaseGroup($releaseGroupId, '削除対象', ReleaseGroupType::Album, true),
        );

        $this->withAuth()
            ->delete(route(ReleaseGroupRouteMap::Delete, $releaseGroupId))
            ->assertStatus(204);

        $this->assertDatabaseCount('release_groups', 0);
    }

    #[Test]
    public function cannotDeleteWhenReleasesExist(): void
    {
        $releaseGroupId = $this->generateUuid();
        $releaseId = $this->generateUuid();

        $this->storeReleaseGroups(
            $this->createReleaseGroup($releaseGroupId, '削除対象', ReleaseGroupType::Album, true),
        );
        $this->storeReleases(
            $this->createRelease($releaseId, $releaseGroupId, '通常盤', true),
        );

        $this->withAuth()
            ->delete(route(ReleaseGroupRouteMap::Delete, $releaseGroupId))
            ->assertStatus(400)
            ->assertJson(['message' => 'リリースが存在するため削除できません。']);

        $this->assertDatabaseCount('release_groups', 1);
    }

    #[Test]
    public function canDeleteEvenIfTargetDoesNotExist(): void
    {
        $this->withAuth()
            ->delete(route(ReleaseGroupRouteMap::Delete, 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'))
            ->assertStatus(204);
    }

    #[Test]
    public function forbidden(): void
    {
        $releaseGroupId = $this->generateUuid();

        $this->storeReleaseGroups(
            $this->createReleaseGroup($releaseGroupId, '削除対象', ReleaseGroupType::Album, true),
        );

        $this->withGeneralAuth()
            ->delete(route(ReleaseGroupRouteMap::Delete, $releaseGroupId))
            ->assertStatus(403);
    }

    #[Test]
    public function invalidId(): void
    {
        $this->withAuth()
            ->delete('/api/admin/v1/release-groups/invalid-id')
            ->assertStatus(404);
    }
}
