<?php

declare(strict_types=1);

namespace Tests\Integration\Song\Application\Admin\UseCase;

use PHPUnit\Framework\Attributes\Test;
use Song\Application\Admin\UseCase\Search\SearchInputData;
use Song\Application\Admin\UseCase\Search\SearchUseCase;
use Song\Domain\Models\SongType;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class SearchUseCaseTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function searchWithoutFilters(): void
    {
        $uuid = $this->generateUuid();

        $this->storeSongs(
            $this->createSong($uuid, 'テスト楽曲', 'テスト楽曲説明', SongType::Original, true, 1, [], [], [], []),
        );

        $result = $this->getInstance()->handle(new SearchInputData());

        $output = $result;

        $this->assertCount(1, $output->songs);
        $this->assertSame($uuid, $output->songs[0]->songId);
        $this->assertSame('テスト楽曲', $output->songs[0]->title);
        $this->assertSame(1, $output->maxPage);
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

        $result = $this->getInstance()->handle(new SearchInputData(title: 'テスト楽曲'));

        $output = $result;

        // 部分一致なので '比較テスト楽曲B' もヒットする
        $this->assertCount(2, $output->songs);
        $this->assertSame($uuid1, $output->songs[0]->songId);
        $this->assertSame('テスト楽曲', $output->songs[0]->title);
        $this->assertSame($uuid2, $output->songs[1]->songId);
        $this->assertSame('比較テスト楽曲B', $output->songs[1]->title);
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

        $result = $this->getInstance()->handle(new SearchInputData(type: SongType::Original->value));

        $output = $result;

        $this->assertCount(1, $output->songs);
        $this->assertSame($uuid1, $output->songs[0]->songId);
        $this->assertSame(SongType::Original, $output->songs[0]->type);
    }

    #[Test]
    public function searchWithTitleNotFound(): void
    {
        $uuid = $this->generateUuid();

        $this->storeSongs(
            $this->createSong($uuid, 'テスト楽曲', 'テスト楽曲説明', SongType::Original, true, 1, [], [], [], []),
        );

        $result = $this->getInstance()->handle(new SearchInputData(title: '存在しないタイトル'));

        $output = $result;

        $this->assertCount(0, $output->songs);
        $this->assertSame(0, $output->maxPage);
    }

    private function getInstance(): SearchUseCase
    {
        $this->privilegedContext();

        return $this->app->make(SearchUseCase::class);
    }
}
