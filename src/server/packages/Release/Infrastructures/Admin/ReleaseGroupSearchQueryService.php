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
        $offset = ($criteria->page - 1) * $criteria->perPage->value;

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
            $query
                ->limit($criteria->perPage->value)
                ->offset($offset),
        );

        return array_map(
            fn (array $row): ReleaseGroupSummary => new ReleaseGroupSummary(
                $this->converter->toUuid(Row::string($row, 'release_group_id')),
                Row::string($row, 'title'),
                Row::int($row, 'type'),
                Row::string($row, 'description'),
                Row::bool($row, 'is_display'),
                Row::int($row, 'order_no'),
                Row::nullableString($row, 'first_released_on'),
            ),
            $rows,
        );
    }

    #[Override]
    public function maxPage(ReleaseGroupSearchCriteria $criteria): int
    {
        $count = Row::intValue($this->buildSearchQuery($criteria)->aggregate($this->queryFactory->pdo(), 'COUNT(*)'));

        return (int)ceil($count / $criteria->perPage->value);
    }

    private function buildSearchQuery(ReleaseGroupSearchCriteria $criteria): SelectBuilder
    {
        $query = $this->queryFactory->select()->from('release_groups');

        if ($criteria->title->isPresent()) {
            $query = $query->where(
                'release_groups.title',
                'LIKE',
                '%' . SqlHelper::escapeLike($criteria->title->get()) . '%',
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

        // リリース未登録のグループは末尾にする
        return $query
            ->orderBy(new Sql('first_released_on IS NULL'))
            ->orderBy('first_released_on', $criteria->order->value)
            ->orderBy('release_groups.order_no', 'desc')
            ->orderBy('release_groups.title');
    }
}
