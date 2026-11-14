<?php

declare(strict_types=1);

namespace Tests\Integration\Tag\Infrastructures;

use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\SongType;
use Support\Domain\SearchCriteria\Order;
use Support\Domain\SearchCriteria\PerPage;
use Support\Optional\None;
use Support\Optional\Some;
use Tag\Domain\Criteria\Sort;
use Tag\Domain\Criteria\TagSearchCriteria;
use Tag\Domain\Models\TagId;
use Tag\Infrastructures\TagRepository;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class TagRepositoryTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function all(): void
    {
        $repository = $this->getInstance();

        $tag1 = $this->createTag($this->generateUuid(), 'テストタグA', 10);
        $tag2 = $this->createTag($this->generateUuid(), 'テストタグB', 20);

        $repository->save($tag1);
        $repository->save($tag2);

        $tags = $repository->all();

        $this->assertCount(2, $tags);
        $this->assertEquals([$tag1, $tag2], $tags);
    }

    #[Test]
    public function save(): void
    {
        $repository = $this->getInstance();

        $tag = $this->createTag($this->generateUuid(), 'テストタグA', 1);

        $repository->save($tag);

        $criteria = new TagSearchCriteria(new Some('テストタグA'));

        $found = $repository->search($criteria);

        $this->assertCount(1, $found);
        $this->assertEquals($tag, $found[0]);
    }

    #[Test]
    public function searchWithoutName(): void
    {
        $repository = $this->getInstance();

        $tag1 = $this->createTag($this->generateUuid(), 'テストタグA', 10);
        $tag2 = $this->createTag($this->generateUuid(), 'テストタグB', 20);

        $repository->save($tag1);
        $repository->save($tag2);

        $criteria = new TagSearchCriteria(new None());

        $tags = $repository->search($criteria);

        $this->assertCount(2, $tags);
        $this->assertEquals([$tag1, $tag2], $tags);
    }

    #[Test]
    public function searchWithName(): void
    {
        $repository = $this->getInstance();

        $tag1 = $this->createTag($this->generateUuid(), 'テストタグA', 10);
        $tag2 = $this->createTag($this->generateUuid(), 'テストタグB', 20);

        $repository->save($tag1);
        $repository->save($tag2);

        $criteria = new TagSearchCriteria(new Some('テストタグA'));

        $tags = $repository->search($criteria);

        $this->assertCount(1, $tags);
        $this->assertEquals($tag1, $tags[0]);
    }

    #[Test]
    public function searchWithNameNotFound(): void
    {
        $repository = $this->getInstance();

        $tag = $this->createTag($this->generateUuid(), 'テストタグA', 10);

        $repository->save($tag);

        $criteria = new TagSearchCriteria(new Some('存在しない'));

        $tags = $repository->search($criteria);

        $this->assertCount(0, $tags);
    }

    #[Test]
    public function searchSortByNameDesc(): void
    {
        $repository = $this->getInstance();

        $tag1 = $this->createTag($this->generateUuid(), 'ソートテストタグA', 10);
        $tag2 = $this->createTag($this->generateUuid(), 'ソートテストタグB', 20);

        $repository->save($tag1);
        $repository->save($tag2);

        $criteria = new TagSearchCriteria(new None(), Sort::Name, Order::Desc);

        $tags = $repository->search($criteria);

        $this->assertCount(2, $tags);
        $this->assertEquals($tag2, $tags[0]);
        $this->assertEquals($tag1, $tags[1]);
    }

    #[Test]
    public function searchWithPagination(): void
    {
        $repository = $this->getInstance();

        $tag1 = $this->createTag($this->generateUuid(), 'テストタグA', 10);
        $tag2 = $this->createTag($this->generateUuid(), 'テストタグB', 20);
        $tag3 = $this->createTag($this->generateUuid(), 'テストタグC', 30);

        $repository->save($tag1);
        $repository->save($tag2);
        $repository->save($tag3);

        $page1 = $repository->search(
            new TagSearchCriteria(new None(), Sort::OrderNo, Order::Asc, 1, PerPage::TwentyFive),
        );
        $page2 = $repository->search(
            new TagSearchCriteria(new None(), Sort::OrderNo, Order::Asc, 2, PerPage::TwentyFive),
        );

        $this->assertCount(3, $page1);
        $this->assertCount(0, $page2);
    }

    #[Test]
    public function maxPageWithoutFilter(): void
    {
        $repository = $this->getInstance();

        $repository->save($this->createTag($this->generateUuid(), 'テストタグA', 10));
        $repository->save($this->createTag($this->generateUuid(), 'テストタグB', 20));

        $criteria = new TagSearchCriteria(new None(), Sort::OrderNo, Order::Asc, 1, PerPage::TwentyFive);

        $this->assertSame(1, $repository->maxPage($criteria));
    }

    #[Test]
    public function maxPageWithNameFilter(): void
    {
        $repository = $this->getInstance();

        $repository->save($this->createTag($this->generateUuid(), 'テストタグA', 10));
        $repository->save($this->createTag($this->generateUuid(), 'テストタグB', 20));

        $criteria = new TagSearchCriteria(new Some('テストタグA'));

        $this->assertSame(1, $repository->maxPage($criteria));
    }

    #[Test]
    public function maxPageWhenEmpty(): void
    {
        $criteria = new TagSearchCriteria(new None());

        $this->assertSame(0, $this->getInstance()->maxPage($criteria));
    }

    #[Test]
    public function isUsed(): void
    {
        $tagId = $this->generateUuid();

        $this->storeTags($this->createTag($tagId, 'テストタグA', 1));
        $this->storeSongs($this->createSong(
            $this->generateUuid(),
            '曲名',
            '説明',
            SongType::Original,
            true,
            1,
            [['tagId' => $tagId, 'orderNo' => 1]],
            [],
            [],
            [],
        ));

        $result = $this->getInstance()->isUsed(new TagId($tagId));

        $this->assertTrue($result);
    }

    #[Test]
    public function isNotUsed(): void
    {
        $tagId = $this->generateUuid();

        $this->storeTags($this->createTag($tagId, 'テストタグA', 1));

        $this->assertFalse($this->getInstance()->isUsed(new TagId($tagId)));
        $this->assertFalse($this->getInstance()->isUsed(new TagId($this->generateUuid())));
    }

    private function getInstance(): TagRepository
    {
        return $this->app->make(TagRepository::class);
    }
}
