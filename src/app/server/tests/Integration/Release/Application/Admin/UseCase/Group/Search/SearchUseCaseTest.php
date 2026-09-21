<?php

declare(strict_types=1);

namespace Tests\Integration\Release\Application\Admin\UseCase\Group\Search;

use DateType\ImmutableDate;
use PHPUnit\Framework\Attributes\Test;
use Release\Application\Admin\UseCase\Group\Search\SearchInputData;
use Release\Application\Admin\UseCase\Group\Search\SearchUseCase;
use Release\Domain\Models\ReleaseGroupType;
use Support\Domain\SearchCriteria\PerPage;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class SearchUseCaseTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

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

        $result = $this->getInstance()->handle(new SearchInputData());

        $releaseGroups = $result->releaseGroups;
        $this->assertCount(3, $releaseGroups);
        $this->assertSame($releaseGroupId2, $releaseGroups[0]->releaseGroupId);
        $this->assertSame('2026-02-01', $releaseGroups[0]->firstReleasedOn);
        $this->assertSame($releaseGroupId1, $releaseGroups[1]->releaseGroupId);
        $this->assertSame('2026-01-01', $releaseGroups[1]->firstReleasedOn);
        $this->assertSame($releaseGroupId3, $releaseGroups[2]->releaseGroupId);
        $this->assertNull($releaseGroups[2]->firstReleasedOn);
        $this->assertSame(1, $result->maxPage);
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

        $result = $this->getInstance()->handle(new SearchInputData(
            title: '春',
            type: ReleaseGroupType::Album->value,
            isDisplay: true,
        ));

        $this->assertCount(1, $result->releaseGroups);
        $this->assertSame($releaseGroupId1, $result->releaseGroups[0]->releaseGroupId);
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

        $result = $this->getInstance()->handle(new SearchInputData());

        $this->assertSame($releaseGroupId2, $result->releaseGroups[0]->releaseGroupId);
        $this->assertSame($releaseGroupId1, $result->releaseGroups[1]->releaseGroupId);
    }

    #[Test]
    public function paginates(): void
    {
        foreach (range(1, 30) as $i) {
            $this->storeReleaseGroups(
                $this->createReleaseGroup($this->generateUuid(), sprintf('作品%02d', $i), ReleaseGroupType::Single, true),
            );
        }

        $result = $this->getInstance()->handle(new SearchInputData(
            page: 2,
            perPage: PerPage::TwentyFive,
        ));

        $this->assertCount(5, $result->releaseGroups);
        $this->assertSame(2, $result->maxPage);
    }

    private function getInstance(): SearchUseCase
    {
        $this->privilegedContext();

        return $this->app->make(SearchUseCase::class);
    }
}
