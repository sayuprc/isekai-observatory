<?php

declare(strict_types=1);

namespace Release\Infrastructures\Admin;

use Emonkak\Orm\SelectBuilder;
use Override;
use Release\Application\Admin\Search\Query\ReleaseSearchQueryServiceInterface;
use Release\Application\Admin\Search\Query\ReleaseSummary;
use Release\Domain\Criteria\ReleaseSearchCriteria;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Infrastructures\Database\QueryFactory;
use Support\Infrastructures\Database\Row;
use Support\Infrastructures\Database\SqlHelper;

readonly class ReleaseSearchQueryService implements ReleaseSearchQueryServiceInterface
{
    public function __construct(
        private QueryFactory $queryFactory,
        private UuidConverterInterface $converter,
    ) {
    }

    #[Override]
    public function search(ReleaseSearchCriteria $criteria): array
    {
        $query = $this
            ->buildSearchQuery($criteria)
            ->withSelect([
                'releases.release_id',
                'releases.release_group_id',
                'releases.name',
                'releases.released_on',
                'releases.is_display',
            ])
            ->select('release_groups.title', 'release_group_title')
            ->select('release_groups.is_display', 'release_group_is_display')
            ->orderBy('releases.released_on', 'desc')
            ->orderBy('release_groups.title')
            ->orderBy('releases.order_no')
            ->orderBy('releases.name');

        $rows = $this->queryFactory->paginate($query, $criteria->page, $criteria->perPage)
            |> $this->queryFactory->fetchAll(...);

        // 提供形態は 1 ページ分のリリース ID でまとめて引き、行ごとの問い合わせを避ける
        $formats = $this->findFormats(array_map(
            static fn (array $row): string => Row::string($row, 'release_id'),
            $rows,
        ));

        return array_map(
            fn (array $row): ReleaseSummary => new ReleaseSummary(
                $this->converter->toUuid(Row::string($row, 'release_id')),
                $this->converter->toUuid(Row::string($row, 'release_group_id')),
                Row::string($row, 'release_group_title'),
                Row::string($row, 'name'),
                Row::string($row, 'released_on'),
                Row::bool($row, 'is_display') && Row::bool($row, 'release_group_is_display'),
                $formats[Row::string($row, 'release_id')] ?? [],
            ),
            $rows,
        );
    }

    #[Override]
    public function maxPage(ReleaseSearchCriteria $criteria): int
    {
        return $this->queryFactory->maxPage($this->buildSearchQuery($criteria), $criteria->perPage);
    }

    private function buildSearchQuery(ReleaseSearchCriteria $criteria): SelectBuilder
    {
        $query = $this->queryFactory
            ->select()
            ->from('releases')
            ->join('release_groups', 'release_groups.release_group_id = releases.release_group_id');

        if ($criteria->releaseGroupTitle->isPresent()) {
            $query = $query->where(
                'release_groups.title_lower',
                'LIKE',
                SqlHelper::containsPattern(mb_strtolower($criteria->releaseGroupTitle->get())),
            );
        }

        return $query;
    }

    /**
     * @param array<int, string> $binReleaseIds
     *
     * @return array<string, list<int>>
     */
    private function findFormats(array $binReleaseIds): array
    {
        if ($binReleaseIds === []) {
            return [];
        }

        $rows = $this->queryFactory
            ->select()
            ->withSelect(['release_id', 'format'])
            ->from('release_formats')
            ->where('release_id', 'IN', $binReleaseIds)
            ->orderBy('format')
            |> $this->queryFactory->fetchAll(...);

        $formats = [];
        foreach ($rows as $row) {
            $formats[Row::string($row, 'release_id')][] = Row::int($row, 'format');
        }

        return $formats;
    }
}
