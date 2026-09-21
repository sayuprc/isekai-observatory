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
        $offset = ($criteria->page - 1) * $criteria->perPage->value;

        $rows = $this->queryFactory->fetchAll(
            $this->buildQuery($criteria)
                ->withSelect(self::COLUMNS)
                ->orderBy($criteria->sort->value, $criteria->order->value)
                ->limit($criteria->perPage->value)
                ->offset($offset),
        );

        return array_map($this->hydrate(...), $rows);
    }

    #[Override]
    public function maxPage(SongSearchCriteria $criteria): int
    {
        $count = Row::intValue($this->buildQuery($criteria)->aggregate($this->queryFactory->pdo(), 'COUNT(*)'));

        return (int)ceil($count / $criteria->perPage->value);
    }

    private function buildQuery(SongSearchCriteria $criteria): SelectBuilder
    {
        $query = $this->queryFactory->select()->from('songs');

        if ($criteria->title->isPresent()) {
            $query = $query->where(
                'title_lower',
                'LIKE',
                '%' . SqlHelper::escapeLike(mb_strtolower($criteria->title->get())) . '%',
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
    private function hydrate(array $row): SongSummary
    {
        return new SongSummary(
            $this->converter->toUuid(Row::string($row, 'song_id')),
            Row::string($row, 'title'),
            SongType::from(Row::int($row, 'type')),
            Row::bool($row, 'is_display'),
            Row::int($row, 'order_no'),
        );
    }
}
