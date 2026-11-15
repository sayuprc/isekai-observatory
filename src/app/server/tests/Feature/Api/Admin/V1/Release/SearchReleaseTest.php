<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Release;

use DateType\ImmutableDate;
use PHPUnit\Framework\Attributes\Test;
use Release\Domain\Models\Release;
use Release\Domain\Models\ReleaseFormat;
use Release\Domain\Models\ReleaseGroupType;
use Release\Route\ReleaseRouteMap;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class SearchReleaseTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;
    use WithAuth;

    #[Test]
    public function returnsReleasesWithTheirGroupInReleasedOnDescendingOrder(): void
    {
        $releaseGroupId1 = $this->generateUuid();
        $releaseGroupId2 = $this->generateUuid();
        $oldReleaseId = $this->generateUuid();
        $newReleaseId = $this->generateUuid();
        $otherGroupReleaseId = $this->generateUuid();

        $this->storeReleaseGroups(
            $this->createReleaseGroup($releaseGroupId1, 'ライブ映像', ReleaseGroupType::Album, true),
            $this->createReleaseGroup($releaseGroupId2, 'シングル', ReleaseGroupType::Single, true),
        );
        $this->storeReleases(
            $this->createRelease(
                $oldReleaseId,
                $releaseGroupId1,
                '通常盤',
                true,
                new ImmutableDate('2026-01-01'),
                formats: [ReleaseFormat::BluRay->value, ReleaseFormat::Dvd->value],
            ),
            $this->createRelease($newReleaseId, $releaseGroupId1, '限定盤', true, new ImmutableDate('2026-03-01')),
            $this->createRelease($otherGroupReleaseId, $releaseGroupId2, '', true, new ImmutableDate('2026-02-01')),
        );

        $this
            ->withAuth()
            ->getJson(route(ReleaseRouteMap::Search))
            ->assertOk()
            ->assertExactJson([
                'releases' => [
                    [
                        'releaseId' => $newReleaseId,
                        'releaseGroupId' => $releaseGroupId1,
                        'releaseGroupTitle' => 'ライブ映像',
                        'name' => '限定盤',
                        'releasedOn' => '2026-03-01',
                        'isDisplay' => true,
                        'formats' => [ReleaseFormat::Cd->value],
                    ],
                    [
                        'releaseId' => $otherGroupReleaseId,
                        'releaseGroupId' => $releaseGroupId2,
                        'releaseGroupTitle' => 'シングル',
                        'name' => '',
                        'releasedOn' => '2026-02-01',
                        'isDisplay' => true,
                        'formats' => [ReleaseFormat::Cd->value],
                    ],
                    [
                        'releaseId' => $oldReleaseId,
                        'releaseGroupId' => $releaseGroupId1,
                        'releaseGroupTitle' => 'ライブ映像',
                        'name' => '通常盤',
                        'releasedOn' => '2026-01-01',
                        'isDisplay' => true,
                        'formats' => [ReleaseFormat::Dvd->value, ReleaseFormat::BluRay->value],
                    ],
                ],
                'maxPage' => 1,
            ]);
    }

    #[Test]
    public function canFilterByReleaseGroupTitle(): void
    {
        $releaseGroupId1 = $this->generateUuid();
        $releaseGroupId2 = $this->generateUuid();
        $matchedReleaseId = $this->generateUuid();

        $this->storeReleaseGroups(
            $this->createReleaseGroup($releaseGroupId1, 'Live Tour 2026', ReleaseGroupType::Album, true),
            $this->createReleaseGroup($releaseGroupId2, 'スタジオアルバム', ReleaseGroupType::Album, true),
        );
        $this->storeReleases(
            $this->createRelease($matchedReleaseId, $releaseGroupId1, '通常盤', true),
            // 版名が検索語を含んでいても、グループのタイトルが合わなければ対象にしない
            $this->createRelease($this->generateUuid(), $releaseGroupId2, 'live 盤', true),
        );

        $this
            ->withAuth()
            ->getJson(route(ReleaseRouteMap::Search, ['title' => 'live']))
            ->assertOk()
            ->assertJsonCount(1, 'releases')
            ->assertJsonPath('releases.0.releaseId', $matchedReleaseId)
            ->assertJsonPath('maxPage', 1);
    }

    #[Test]
    public function isDisplayIsTrueOnlyWhenBothReleaseAndGroupAreDisplayed(): void
    {
        $displayedGroupId = $this->generateUuid();
        $hiddenGroupId = $this->generateUuid();
        $displayedReleaseId = $this->generateUuid();
        $hiddenReleaseId = $this->generateUuid();
        $releaseInHiddenGroupId = $this->generateUuid();

        $this->storeReleaseGroups(
            $this->createReleaseGroup($displayedGroupId, '公開の作品', ReleaseGroupType::Album, true),
            $this->createReleaseGroup($hiddenGroupId, '非公開の作品', ReleaseGroupType::Album, false),
        );
        $this->storeReleases(
            $this->createRelease($displayedReleaseId, $displayedGroupId, '公開', true, new ImmutableDate('2026-03-01')),
            $this->createRelease($hiddenReleaseId, $displayedGroupId, '非公開', false, new ImmutableDate('2026-02-01')),
            $this->createRelease(
                $releaseInHiddenGroupId,
                $hiddenGroupId,
                '公開',
                true,
                new ImmutableDate('2026-01-01'),
            ),
        );

        $this
            ->withAuth()
            ->getJson(route(ReleaseRouteMap::Search))
            ->assertOk()
            ->assertJsonPath('releases.0.releaseId', $displayedReleaseId)
            ->assertJsonPath('releases.0.isDisplay', true)
            ->assertJsonPath('releases.1.releaseId', $hiddenReleaseId)
            ->assertJsonPath('releases.1.isDisplay', false)
            ->assertJsonPath('releases.2.releaseId', $releaseInHiddenGroupId)
            ->assertJsonPath('releases.2.isDisplay', false);
    }

    #[Test]
    public function paginatesByRelease(): void
    {
        $releaseGroupId = $this->generateUuid();
        $oldestReleaseId = $this->generateUuid();

        // グループは 1 件でも、版が 1 ページ分を超えれば次のページに送られる
        $this->storeReleaseGroups($this->createReleaseGroup($releaseGroupId, '作品', ReleaseGroupType::Album, true));
        $this->storeReleases(
            $this->createRelease($oldestReleaseId, $releaseGroupId, '最古', true, new ImmutableDate('2025-01-01')),
            ...array_map(
                fn (int $day): Release => $this->createRelease(
                    $this->generateUuid(),
                    $releaseGroupId,
                    "版{$day}",
                    true,
                    new ImmutableDate(sprintf('2026-01-%02d', $day)),
                ),
                range(1, 25),
            ),
        );

        $this
            ->withAuth()
            ->getJson(route(ReleaseRouteMap::Search, ['page' => 2, 'per_page' => 25]))
            ->assertOk()
            ->assertJsonCount(1, 'releases')
            ->assertJsonPath('releases.0.releaseId', $oldestReleaseId)
            ->assertJsonPath('maxPage', 2);
    }
}
