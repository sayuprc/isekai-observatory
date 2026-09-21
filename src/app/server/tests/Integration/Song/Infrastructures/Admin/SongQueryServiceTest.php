<?php

declare(strict_types=1);

namespace Tests\Integration\Song\Infrastructures\Admin;

use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Criteria\SongSearchCriteria;
use Song\Domain\Criteria\Sort;
use Song\Domain\Models\SongType;
use Song\Infrastructures\Admin\SongQueryService;
use Support\Domain\SearchCriteria\Order;
use Support\Domain\SearchCriteria\PerPage;
use Support\Optional\None;
use Support\Optional\Some;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class SongQueryServiceTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function searchWithoutFilters(): void
    {
        $uuid1 = $this->generateUuid();
        $uuid2 = $this->generateUuid();

        $this->storeSongs(
            $this->createSong($uuid1, 'テスト楽曲', 'テスト楽曲説明', SongType::Original, true, 1, [], [], [], []),
            $this->createSong($uuid2, '比較テスト楽曲B', '比較テスト楽曲B説明', SongType::Cover, true, 2, [], [], [], []),
        );

        $results = $this->getInstance()->search($this->criteria());

        $this->assertCount(2, $results);
        $this->assertSame($uuid1, $results[0]->songId);
        $this->assertSame('テスト楽曲', $results[0]->title);
        $this->assertSame(SongType::Original, $results[0]->type);
        $this->assertTrue($results[0]->isDisplay);
        $this->assertSame(1, $results[0]->orderNo);
        $this->assertSame($uuid2, $results[1]->songId);
    }

    #[Test]
    public function searchWithTitle(): void
    {
        $uuid1 = $this->generateUuid();
        $uuid2 = $this->generateUuid();

        $this->storeSongs(
            $this->createSong($uuid1, 'テスト楽曲', 'テスト楽曲説明', SongType::Original, true, 1, [], [], [], []),
            $this->createSong($uuid2, '比較テスト楽曲B', '比較テスト楽曲B説明', SongType::Cover, true, 2, [], [], [], []),
        );

        $results = $this->getInstance()->search($this->criteria(title: new Some('テスト楽曲')));

        // 部分一致なので '比較テスト楽曲B' もヒットする
        $this->assertCount(2, $results);
        $this->assertSame($uuid1, $results[0]->songId);
        $this->assertSame('テスト楽曲', $results[0]->title);
        $this->assertSame($uuid2, $results[1]->songId);
        $this->assertSame('比較テスト楽曲B', $results[1]->title);
    }

    #[Test]
    public function searchWithType(): void
    {
        $uuid1 = $this->generateUuid();
        $uuid2 = $this->generateUuid();

        $this->storeSongs(
            $this->createSong($uuid1, 'テスト楽曲', 'テスト楽曲説明', SongType::Original, true, 1, [], [], [], []),
            $this->createSong($uuid2, '比較テスト楽曲B', '比較テスト楽曲B説明', SongType::Cover, true, 2, [], [], [], []),
        );

        $results = $this->getInstance()->search($this->criteria(type: new Some(SongType::Original)));

        $this->assertCount(1, $results);
        $this->assertSame($uuid1, $results[0]->songId);
        $this->assertSame(SongType::Original, $results[0]->type);
    }

    #[Test]
    public function searchNotFound(): void
    {
        $uuid = $this->generateUuid();

        $this->storeSongs(
            $this->createSong($uuid, 'テスト楽曲', 'テスト楽曲説明', SongType::Original, true, 1, [], [], [], []),
        );

        $results = $this->getInstance()->search($this->criteria(title: new Some('存在しないタイトル')));

        $this->assertCount(0, $results);
    }

    #[Test]
    public function searchWithIsDisplay(): void
    {
        $uuid1 = $this->generateUuid();
        $uuid2 = $this->generateUuid();

        $this->storeSongs(
            $this->createSong($uuid1, 'テスト楽曲', 'テスト楽曲説明', SongType::Original, true, 1, [], [], [], []),
            $this->createSong($uuid2, '比較テスト楽曲B', '比較テスト楽曲B説明', SongType::Cover, false, 2, [], [], [], []),
        );

        $results = $this->getInstance()->search($this->criteria(isDisplay: new Some(false)));

        $this->assertCount(1, $results);
        $this->assertSame($uuid2, $results[0]->songId);
        $this->assertFalse($results[0]->isDisplay);
    }

    #[Test]
    public function maxPage(): void
    {
        $songs = [];

        for ($i = 1; $i <= 26; $i++) {
            $songs[] = $this->createSong($this->generateUuid(), "楽曲{$i}", '説明', SongType::Original, true, $i, [], [], [], []);
        }

        $this->storeSongs(...$songs);

        $this->assertSame(2, $this->getInstance()->maxPage($this->criteria(perPage: PerPage::TwentyFive)));
    }

    #[Test]
    public function maxPageWhenNotFound(): void
    {
        $criteria = $this->criteria(title: new Some('存在しないタイトル'));

        $this->assertSame(0, $this->getInstance()->maxPage($criteria));
    }

    private function criteria(
        mixed $title = null,
        mixed $type = null,
        mixed $isDisplay = null,
        PerPage $perPage = PerPage::Fifty,
    ): SongSearchCriteria {
        return new SongSearchCriteria(
            $title ?? new None(),
            $type ?? new None(),
            $isDisplay ?? new None(),
            Sort::OrderNo,
            Order::Asc,
            1,
            $perPage,
        );
    }

    private function getInstance(): SongQueryService
    {
        return $this->app->make(SongQueryService::class);
    }
}
