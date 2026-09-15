<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\ReleaseGroup;

use DateType\ImmutableDate;
use PHPUnit\Framework\Attributes\Test;
use Release\Domain\Models\ReleaseGroupType;
use Release\Route\ReleaseGroupRouteMap;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class SearchReleaseGroupTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;
    use WithAuth;

    #[Test]
    public function sortsByFirstReleasedOnDescAndGroupsWithoutReleasesComeLast(): void
    {
        $releaseGroupId1 = $this->generateUuid();
        $releaseGroupId2 = $this->generateUuid();
        $releaseGroupId3 = $this->generateUuid();

        $this->storeReleaseGroups(
            $this->createReleaseGroup($releaseGroupId1, '古い作品', ReleaseGroupType::Single, true),
            $this->createReleaseGroup($releaseGroupId2, '新しい作品', ReleaseGroupType::Album, true),
            $this->createReleaseGroup($releaseGroupId3, 'リリース未登録の作品', ReleaseGroupType::Ep, true),
        );
        $this->storeReleases(
            // 古い作品: 配信が先行、CD が後発。最古 2026-01-01 がグループの代表日になる
            $this->createRelease($this->generateUuid(), $releaseGroupId1, '配信', true, new ImmutableDate('2026-01-01'), media: [
                ['position' => 1, 'name' => null, 'tracks' => []],
            ]),
            $this->createRelease($this->generateUuid(), $releaseGroupId1, 'CD', true, new ImmutableDate('2026-03-01'), media: [
                ['position' => 1, 'name' => null, 'tracks' => []],
            ]),
            $this->createRelease($this->generateUuid(), $releaseGroupId2, '配信', true, new ImmutableDate('2026-02-01'), media: [
                ['position' => 1, 'name' => null, 'tracks' => []],
            ]),
        );

        $this->withAuth()
            ->getJson(route(ReleaseGroupRouteMap::Search))
            ->assertStatus(200)
            ->assertExactJson([
                'releaseGroups' => [
                    [
                        'releaseGroupId' => $releaseGroupId2,
                        'title' => '新しい作品',
                        'typeValue' => ReleaseGroupType::Album->value,
                        'description' => 'テスト用リリースグループ',
                        'isDisplay' => true,
                        'orderNo' => 1,
                        'firstReleasedOn' => '2026-02-01',
                    ],
                    [
                        'releaseGroupId' => $releaseGroupId1,
                        'title' => '古い作品',
                        'typeValue' => ReleaseGroupType::Single->value,
                        'description' => 'テスト用リリースグループ',
                        'isDisplay' => true,
                        'orderNo' => 1,
                        'firstReleasedOn' => '2026-01-01',
                    ],
                    [
                        'releaseGroupId' => $releaseGroupId3,
                        'title' => 'リリース未登録の作品',
                        'typeValue' => ReleaseGroupType::Ep->value,
                        'description' => 'テスト用リリースグループ',
                        'isDisplay' => true,
                        'orderNo' => 1,
                        'firstReleasedOn' => null,
                    ],
                ],
                'maxPage' => 1,
            ]);
    }

    #[Test]
    public function sortsByFirstReleasedOnBeforeOrderNo(): void
    {
        $releaseGroupId1 = $this->generateUuid();
        $releaseGroupId2 = $this->generateUuid();

        $this->storeReleaseGroups(
            $this->createReleaseGroup($releaseGroupId1, '古いが先頭の作品', ReleaseGroupType::Single, true, orderNo: 1),
            $this->createReleaseGroup($releaseGroupId2, '新しいが後続の作品', ReleaseGroupType::Album, true, orderNo: 2),
        );
        $this->storeReleases(
            $this->createRelease($this->generateUuid(), $releaseGroupId1, '配信', true, new ImmutableDate('2026-01-01'), media: [
                ['position' => 1, 'name' => null, 'tracks' => []],
            ]),
            $this->createRelease($this->generateUuid(), $releaseGroupId2, '配信', true, new ImmutableDate('2026-02-01'), media: [
                ['position' => 1, 'name' => null, 'tracks' => []],
            ]),
        );

        $this->withAuth()
            ->getJson(route(ReleaseGroupRouteMap::Search))
            ->assertStatus(200)
            ->assertJsonPath('releaseGroups.0.releaseGroupId', $releaseGroupId2)
            ->assertJsonPath('releaseGroups.1.releaseGroupId', $releaseGroupId1);
    }

    #[Test]
    public function sortsByOrderNoDescendingWhenFirstReleasedOnIsSame(): void
    {
        $releaseGroupId1 = $this->generateUuid();
        $releaseGroupId2 = $this->generateUuid();

        $this->storeReleaseGroups(
            $this->createReleaseGroup($releaseGroupId1, '同日で order_no が小さい作品', ReleaseGroupType::Single, true, orderNo: 1),
            $this->createReleaseGroup($releaseGroupId2, '同日で order_no が大きい作品', ReleaseGroupType::Album, true, orderNo: 2),
        );
        $this->storeReleases(
            $this->createRelease($this->generateUuid(), $releaseGroupId1, '配信', true, new ImmutableDate('2026-01-01'), media: [
                ['position' => 1, 'name' => null, 'tracks' => []],
            ]),
            $this->createRelease($this->generateUuid(), $releaseGroupId2, '配信', true, new ImmutableDate('2026-01-01'), media: [
                ['position' => 1, 'name' => null, 'tracks' => []],
            ]),
        );

        $this->withAuth()
            ->getJson(route(ReleaseGroupRouteMap::Search))
            ->assertStatus(200)
            ->assertJsonPath('releaseGroups.0.releaseGroupId', $releaseGroupId2)
            ->assertJsonPath('releaseGroups.1.releaseGroupId', $releaseGroupId1);
    }

    #[Test]
    public function canFilterByTitleAndTypeAndIsDisplay(): void
    {
        $releaseGroupId1 = $this->generateUuid();
        $releaseGroupId2 = $this->generateUuid();
        $releaseGroupId3 = $this->generateUuid();

        $this->storeReleaseGroups(
            $this->createReleaseGroup($releaseGroupId1, '観測された春', ReleaseGroupType::Album, true),
            $this->createReleaseGroup($releaseGroupId2, '観測された夏', ReleaseGroupType::Single, true),
            $this->createReleaseGroup($releaseGroupId3, '非表示の春', ReleaseGroupType::Album, false),
        );

        $this->withAuth()
            ->getJson(route(ReleaseGroupRouteMap::Search) . '?title=春&type=2&is_display=1')
            ->assertStatus(200)
            ->assertJsonCount(1, 'releaseGroups')
            ->assertJsonPath('releaseGroups.0.releaseGroupId', $releaseGroupId1);
    }

    #[Test]
    public function canSortByFirstReleasedOnAscending(): void
    {
        $releaseGroupId1 = $this->generateUuid();
        $releaseGroupId2 = $this->generateUuid();
        $releaseGroupId3 = $this->generateUuid();

        $this->storeReleaseGroups(
            $this->createReleaseGroup($releaseGroupId1, '古い作品', ReleaseGroupType::Single, true),
            $this->createReleaseGroup($releaseGroupId2, '新しい作品', ReleaseGroupType::Album, true),
            $this->createReleaseGroup($releaseGroupId3, 'リリース未登録の作品', ReleaseGroupType::Ep, true),
        );
        $this->storeReleases(
            $this->createRelease($this->generateUuid(), $releaseGroupId1, '配信', true, new ImmutableDate('2026-01-01'), media: [
                ['position' => 1, 'name' => null, 'tracks' => []],
            ]),
            $this->createRelease($this->generateUuid(), $releaseGroupId2, '配信', true, new ImmutableDate('2026-02-01'), media: [
                ['position' => 1, 'name' => null, 'tracks' => []],
            ]),
        );

        $this->withAuth()
            ->getJson(route(ReleaseGroupRouteMap::Search, ['sort' => 'first_released_on', 'order' => 'asc']))
            ->assertStatus(200)
            ->assertJsonPath('releaseGroups.0.releaseGroupId', $releaseGroupId1)
            ->assertJsonPath('releaseGroups.1.releaseGroupId', $releaseGroupId2)
            ->assertJsonPath('releaseGroups.2.releaseGroupId', $releaseGroupId3);
    }

    #[Test]
    public function canSortByTitle(): void
    {
        $releaseGroupId1 = $this->generateUuid();
        $releaseGroupId2 = $this->generateUuid();

        $this->storeReleaseGroups(
            $this->createReleaseGroup($releaseGroupId1, 'い作品', ReleaseGroupType::Single, true),
            $this->createReleaseGroup($releaseGroupId2, 'あ作品', ReleaseGroupType::Album, true),
        );

        $this->withAuth()
            ->getJson(route(ReleaseGroupRouteMap::Search, ['sort' => 'title', 'order' => 'asc']))
            ->assertStatus(200)
            ->assertJsonPath('releaseGroups.0.releaseGroupId', $releaseGroupId2)
            ->assertJsonPath('releaseGroups.1.releaseGroupId', $releaseGroupId1);
    }
}
