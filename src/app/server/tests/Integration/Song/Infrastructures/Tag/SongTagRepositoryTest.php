<?php

declare(strict_types=1);

namespace Tests\Integration\Song\Infrastructures\Tag;

use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Criteria\Tag\SongTagSearchCriteria;
use Song\Domain\Criteria\Tag\SongTagSort;
use Song\Domain\Models\SongType;
use Song\Domain\Models\Tag\SongTagId;
use Song\Infrastructures\Tag\SongTagRepository;
use Support\Domain\SearchCriteria\Order;
use Support\Domain\SearchCriteria\PerPage;
use Support\Optional\None;
use Support\Optional\Some;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class SongTagRepositoryTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function all(): void
    {
        $repository = $this->getInstance();

        $songTag1 = $this->createSongTag($this->generateUuid(), 'テストタグA', 10);
        $songTag2 = $this->createSongTag($this->generateUuid(), 'テストタグB', 20);

        $repository->save($songTag1);
        $repository->save($songTag2);

        $songTags = $repository->all();

        $this->assertCount(2, $songTags);
        $this->assertEquals([$songTag1, $songTag2], $songTags);
    }

    #[Test]
    public function save(): void
    {
        $repository = $this->getInstance();

        $songTag = $this->createSongTag($this->generateUuid(), 'テストタグA', 1);

        $repository->save($songTag);

        $criteria = new SongTagSearchCriteria(new Some('テストタグA'));

        $found = $repository->search($criteria);

        $this->assertCount(1, $found);
        $this->assertEquals($songTag, $found[0]);
    }

    #[Test]
    public function searchWithoutName(): void
    {
        $repository = $this->getInstance();

        $songTag1 = $this->createSongTag($this->generateUuid(), 'テストタグA', 10);
        $songTag2 = $this->createSongTag($this->generateUuid(), 'テストタグB', 20);

        $repository->save($songTag1);
        $repository->save($songTag2);

        $criteria = new SongTagSearchCriteria(new None());

        $songTags = $repository->search($criteria);

        $this->assertCount(2, $songTags);
        $this->assertEquals([$songTag1, $songTag2], $songTags);
    }

    #[Test]
    public function searchWithName(): void
    {
        $repository = $this->getInstance();

        $songTag1 = $this->createSongTag($this->generateUuid(), 'テストタグA', 10);
        $songTag2 = $this->createSongTag($this->generateUuid(), 'テストタグB', 20);

        $repository->save($songTag1);
        $repository->save($songTag2);

        $criteria = new SongTagSearchCriteria(new Some('テストタグA'));

        $songTags = $repository->search($criteria);

        $this->assertCount(1, $songTags);
        $this->assertEquals($songTag1, $songTags[0]);
    }

    #[Test]
    public function searchWithNameNotFound(): void
    {
        $repository = $this->getInstance();

        $songTag = $this->createSongTag($this->generateUuid(), 'テストタグA', 10);

        $repository->save($songTag);

        $criteria = new SongTagSearchCriteria(new Some('存在しない'));

        $songTags = $repository->search($criteria);

        $this->assertCount(0, $songTags);
    }

    #[Test]
    public function searchSortByNameDesc(): void
    {
        $repository = $this->getInstance();

        $songTag1 = $this->createSongTag($this->generateUuid(), 'ソートテストタグA', 10);
        $songTag2 = $this->createSongTag($this->generateUuid(), 'ソートテストタグB', 20);

        $repository->save($songTag1);
        $repository->save($songTag2);

        $criteria = new SongTagSearchCriteria(new None(), SongTagSort::Name, Order::Desc);

        $songTags = $repository->search($criteria);

        $this->assertCount(2, $songTags);
        $this->assertEquals($songTag2, $songTags[0]);
        $this->assertEquals($songTag1, $songTags[1]);
    }

    #[Test]
    public function searchWithPagination(): void
    {
        $repository = $this->getInstance();

        $songTag1 = $this->createSongTag($this->generateUuid(), 'テストタグA', 10);
        $songTag2 = $this->createSongTag($this->generateUuid(), 'テストタグB', 20);
        $songTag3 = $this->createSongTag($this->generateUuid(), 'テストタグC', 30);

        $repository->save($songTag1);
        $repository->save($songTag2);
        $repository->save($songTag3);

        $page1 = $repository->search(new SongTagSearchCriteria(new None(), SongTagSort::OrderNo, Order::Asc, 1, PerPage::TwentyFive));
        $page2 = $repository->search(new SongTagSearchCriteria(new None(), SongTagSort::OrderNo, Order::Asc, 2, PerPage::TwentyFive));

        $this->assertCount(3, $page1);
        $this->assertCount(0, $page2);
    }

    #[Test]
    public function maxPageWithoutFilter(): void
    {
        $repository = $this->getInstance();

        $repository->save($this->createSongTag($this->generateUuid(), 'テストタグA', 10));
        $repository->save($this->createSongTag($this->generateUuid(), 'テストタグB', 20));

        $criteria = new SongTagSearchCriteria(new None(), SongTagSort::OrderNo, Order::Asc, 1, PerPage::TwentyFive);

        $this->assertSame(1, $repository->maxPage($criteria));
    }

    #[Test]
    public function maxPageWithNameFilter(): void
    {
        $repository = $this->getInstance();

        $repository->save($this->createSongTag($this->generateUuid(), 'テストタグA', 10));
        $repository->save($this->createSongTag($this->generateUuid(), 'テストタグB', 20));

        $criteria = new SongTagSearchCriteria(new Some('テストタグA'));

        $this->assertSame(1, $repository->maxPage($criteria));
    }

    #[Test]
    public function maxPageWhenEmpty(): void
    {
        $criteria = new SongTagSearchCriteria(new None());

        $this->assertSame(0, $this->getInstance()->maxPage($criteria));
    }

    #[Test]
    public function isUsed(): void
    {
        $songTagId = $this->generateUuid();

        $this->storeSongTags($this->createSongTag($songTagId, 'テストタグA', 1));
        $this->storeSongs($this->createSong(
            $this->generateUuid(),
            '曲名',
            '説明',
            SongType::Original,
            true,
            1,
            [['songTagId' => $songTagId, 'orderNo' => 1]],
            [],
            [],
            [],
        ));

        $result = $this->getInstance()->isUsed(new SongTagId($songTagId));

        $this->assertTrue($result);
    }

    #[Test]
    public function isNotUsed(): void
    {
        $songTagId = $this->generateUuid();

        $this->storeSongTags($this->createSongTag($songTagId, 'テストタグA', 1));

        $this->assertFalse($this->getInstance()->isUsed(new SongTagId($songTagId)));
        $this->assertFalse($this->getInstance()->isUsed(new SongTagId($this->generateUuid())));
    }

    private function getInstance(): SongTagRepository
    {
        return $this->app->make(SongTagRepository::class);
    }
}
