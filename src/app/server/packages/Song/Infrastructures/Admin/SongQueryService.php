<?php

declare(strict_types=1);

namespace Song\Infrastructures\Admin;

use Emonkak\Orm\SelectBuilder;
use Override;
use Song\Application\Admin\Query\SongQueryServiceInterface;
use Song\Application\Admin\Query\SongSummary;
use Song\Domain\Criteria\SongSearchCriteria;
use Song\Domain\Models\SongType;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Infrastructures\Database\QueryFactory;
use Support\Infrastructures\Database\Row;
use Support\Infrastructures\Database\SqlHelper;

readonly class SongQueryService implements SongQueryServiceInterface
{
    /** @var list<string> */
    private const array COLUMNS = ['song_id', 'title', 'type', 'is_display', 'order_no'];

    public function __construct(
        private QueryFactory $queryFactory,
        private UuidConverterInterface $converter,
    ) {
    }

    #[Override]
    public function search(SongSearchCriteria $criteria): array
    {
        $rows = $this->queryFactory->fetchAll(
            $this->queryFactory->paginate(
                $this->buildQuery($criteria)
                    ->withSelect(self::COLUMNS)
                    ->orderBy($criteria->sort->value, $criteria->order->value),
                $criteria->page,
                $criteria->perPage,
            ),
        );

        // 件数は 1 ページ分の楽曲 ID でまとめて引き、行ごとの問い合わせを避ける
        $binSongIds = array_map(static fn (array $row): string => Row::string($row, 'song_id'), $rows);
        $performanceCounts = $this->queryFactory->countBy('song_performances', 'song_id', $binSongIds);
        $mediaCounts = $this->queryFactory->countBy('song_media_links', 'song_id', $binSongIds);
        $personCounts = $this->queryFactory->countBy('song_persons', 'song_id', $binSongIds, 'person_id');
        $releaseCounts = $this->queryFactory->countBy('release_tracks', 'song_id', $binSongIds, 'release_id');

        return array_map(
            fn (array $row): SongSummary => $this->hydrate(
                $row,
                $performanceCounts[Row::string($row, 'song_id')] ?? 0,
                $mediaCounts[Row::string($row, 'song_id')] ?? 0,
                $personCounts[Row::string($row, 'song_id')] ?? 0,
                $releaseCounts[Row::string($row, 'song_id')] ?? 0,
            ),
            $rows,
        );
    }

    #[Override]
    public function maxPage(SongSearchCriteria $criteria): int
    {
        return $this->queryFactory->maxPage($this->buildQuery($criteria), $criteria->perPage);
    }

    private function buildQuery(SongSearchCriteria $criteria): SelectBuilder
    {
        $query = $this->queryFactory->select()->from('songs');

        if ($criteria->title->isPresent()) {
            $query = $query->where(
                'title_lower',
                'LIKE',
                SqlHelper::containsPattern(mb_strtolower($criteria->title->get())),
            );
        }

        if ($criteria->type->isPresent()) {
            $query = $query->where('type', '=', $criteria->type->get()->value);
        }

        if ($criteria->isDisplay->isPresent()) {
            $query = $query->where('is_display', '=', $criteria->isDisplay->get());
        }

        return $query;
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrate(array $row, int $performanceCount, int $mediaCount, int $personCount, int $releaseCount): SongSummary
    {
        return new SongSummary(
            $this->converter->toUuid(Row::string($row, 'song_id')),
            Row::string($row, 'title'),
            SongType::from(Row::int($row, 'type')),
            Row::bool($row, 'is_display'),
            Row::int($row, 'order_no'),
            $performanceCount,
            $mediaCount,
            $personCount,
            $releaseCount,
        );
    }
}
