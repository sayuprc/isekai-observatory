<?php

declare(strict_types=1);

namespace Release\Infrastructures\Admin;

use Emonkak\Orm\SelectBuilder;
use Emonkak\Orm\Sql;
use Override;
use Release\Application\Admin\Query\ReleaseGroupSearchQueryServiceInterface;
use Release\Application\Admin\Query\ReleaseGroupSummary;
use Release\Domain\Criteria\ReleaseGroupSearchCriteria;
use Release\Domain\Criteria\Sort;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Infrastructures\Database\QueryFactory;
use Support\Infrastructures\Database\Row;
use Support\Infrastructures\Database\SqlHelper;

readonly class ReleaseGroupSearchQueryService implements ReleaseGroupSearchQueryServiceInterface
{
    public function __construct(
        private QueryFactory $queryFactory,
        private UuidConverterInterface $converter,
    ) {
    }

    #[Override]
    public function search(ReleaseGroupSearchCriteria $criteria): array
    {
        $query = $this->applyOrder(
            $this->buildSearchQuery($criteria)
                ->withSelect([
                    'release_groups.release_group_id',
                    'release_groups.title',
                    'release_groups.type',
                    'release_groups.description',
                    'release_groups.is_display',
                    'release_groups.order_no',
                ])
                ->select(new Sql('MIN(releases.released_on)'), 'first_released_on')
                ->outerJoin('releases', 'releases.release_group_id = release_groups.release_group_id')
                ->groupBy('release_groups.release_group_id')
                ->groupBy('release_groups.title')
                ->groupBy('release_groups.type')
                ->groupBy('release_groups.description')
                ->groupBy('release_groups.is_display')
                ->groupBy('release_groups.order_no'),
            $criteria,
        );

        $rows = $this->queryFactory->fetchAll(
            $this->queryFactory->paginate($query, $criteria->page, $criteria->perPage),
        );

        // 件数は 1 ページ分のグループ ID でまとめて引き、行ごとの問い合わせを避ける
        $binGroupIds = array_map(static fn (array $row): string => Row::string($row, 'release_group_id'), $rows);
        $releaseCounts = $this->queryFactory->countBy('releases', 'release_group_id', $binGroupIds);
        $songCounts = $this->countSongs($binGroupIds);

        return array_map(
            fn (array $row): ReleaseGroupSummary => new ReleaseGroupSummary(
                $this->converter->toUuid(Row::string($row, 'release_group_id')),
                Row::string($row, 'title'),
                Row::int($row, 'type'),
                Row::string($row, 'description'),
                Row::bool($row, 'is_display'),
                Row::int($row, 'order_no'),
                Row::nullableString($row, 'first_released_on'),
                $releaseCounts[Row::string($row, 'release_group_id')] ?? 0,
                $songCounts[Row::string($row, 'release_group_id')] ?? 0,
            ),
            $rows,
        );
    }

    /**
     * グループごとに、傘下のリリースへ収録された管理対象の楽曲を重複なく数える
     * タイトルだけのトラック (song_id が NULL) は COUNT(DISTINCT) で除かれる
     *
     * @param list<string> $binGroupIds
     *
     * @return array<string, int>
     */
    private function countSongs(array $binGroupIds): array
    {
        if ($binGroupIds === []) {
            return [];
        }

        $counts = [];
        foreach ($this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->select('releases.release_group_id')
                ->select(new Sql('COUNT(DISTINCT release_tracks.song_id)'), 'song_count')
                ->from('release_tracks')
                ->join('releases', 'releases.release_id = release_tracks.release_id')
                ->where('releases.release_group_id', 'IN', $binGroupIds)
                ->groupBy('releases.release_group_id'),
        ) as $row) {
            $counts[Row::string($row, 'release_group_id')] = Row::int($row, 'song_count');
        }

        return $counts;
    }

    #[Override]
    public function maxPage(ReleaseGroupSearchCriteria $criteria): int
    {
        return $this->queryFactory->maxPage($this->buildSearchQuery($criteria), $criteria->perPage);
    }

    private function buildSearchQuery(ReleaseGroupSearchCriteria $criteria): SelectBuilder
    {
        $query = $this->queryFactory->select()->from('release_groups');

        if ($criteria->title->isPresent()) {
            $query = $query->where(
                'release_groups.title_lower',
                'LIKE',
                SqlHelper::containsPattern(mb_strtolower($criteria->title->get())),
            );
        }

        if ($criteria->type->isPresent()) {
            $query = $query->where('release_groups.type', '=', $criteria->type->get()->value);
        }

        if ($criteria->isDisplay->isPresent()) {
            $query = $query->where('release_groups.is_display', '=', $criteria->isDisplay->get());
        }

        return $query;
    }

    private function applyOrder(SelectBuilder $query, ReleaseGroupSearchCriteria $criteria): SelectBuilder
    {
        if ($criteria->sort === Sort::Title) {
            return $query
                ->orderBy('release_groups.title', $criteria->order->value)
                ->orderBy('first_released_on', 'desc')
                ->orderBy('release_groups.order_no', 'desc');
        }

        // MySQL には NULLS LAST が無いため、IS NULL で並べてリリース未登録のグループを昇順・降順どちらでも末尾にする
        return $query
            ->orderBy(new Sql('first_released_on IS NULL'))
            ->orderBy('first_released_on', $criteria->order->value)
            ->orderBy('release_groups.order_no', 'desc')
            ->orderBy('release_groups.title');
    }
}
