<?php

declare(strict_types=1);

namespace Song\Infrastructures\Tag;

use Emonkak\Orm\SelectBuilder;
use Override;
use Song\Domain\Criteria\Tag\SongTagSearchCriteria;
use Song\Domain\Models\Tag\SongTag;
use Song\Domain\Models\Tag\SongTagId;
use Song\Domain\Models\Tag\SongTagName;
use Song\Domain\Models\Tag\SongTagRepositoryInterface;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Infrastructures\Database\QueryFactory;
use Support\Infrastructures\Database\Row;
use Support\Infrastructures\Database\SqlHelper;

readonly class SongTagRepository implements SongTagRepositoryInterface
{
    private const string TABLE = 'song_tags';

    /** @var list<string> */
    private const array COLUMNS = ['song_tag_id', 'name', 'order_no'];

    public function __construct(
        private QueryFactory $queryFactory,
        private UuidConverterInterface $converter,
    ) {
    }

    #[Override]
    public function all(): array
    {
        $rows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(self::COLUMNS)
                ->from(self::TABLE)
                ->orderBy('order_no'),
        );

        return array_map($this->hydrate(...), $rows);
    }

    #[Override]
    public function search(SongTagSearchCriteria $criteria): array
    {
        $offset = ($criteria->page - 1) * $criteria->perPage->value;

        $rows = $this->queryFactory->fetchAll(
            $this->applyNameFilter(
                $this->queryFactory->select()->withSelect(self::COLUMNS)->from(self::TABLE),
                $criteria,
            )
                ->orderBy($criteria->sort->value, $criteria->order->value)
                ->limit($criteria->perPage->value)
                ->offset($offset),
        );

        return array_map($this->hydrate(...), $rows);
    }

    #[Override]
    public function maxPage(SongTagSearchCriteria $criteria): int
    {
        $count = Row::intValue(
            $this->applyNameFilter($this->queryFactory->select()->from(self::TABLE), $criteria)
                ->aggregate($this->queryFactory->pdo(), 'COUNT(*)'),
        );

        return (int)ceil($count / $criteria->perPage->value);
    }

    #[Override]
    public function find(SongTagId $songTagId): ?SongTag
    {
        $rows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(self::COLUMNS)
                ->from(self::TABLE)
                ->where('song_tag_id', '=', $this->converter->toBin($songTagId->value))
                ->limit(1),
        );

        $row = $rows[0] ?? null;

        return is_null($row) ? null : $this->hydrate($row);
    }

    #[Override]
    public function findByName(SongTagName $name): ?SongTag
    {
        $rows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(self::COLUMNS)
                ->from(self::TABLE)
                ->where('name', '=', $name->value)
                ->limit(1),
        );

        $row = $rows[0] ?? null;

        return is_null($row) ? null : $this->hydrate($row);
    }

    #[Override]
    public function findByIds(SongTagId ...$songTagIds): array
    {
        if ($songTagIds === []) {
            return [];
        }

        $binIds = array_map(fn (SongTagId $songTagId): string => $this->converter->toBin($songTagId->value), $songTagIds);

        $rows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(self::COLUMNS)
                ->from(self::TABLE)
                ->where('song_tag_id', 'IN', $binIds)
                ->orderBy('order_no'),
        );

        return array_map($this->hydrate(...), $rows);
    }

    #[Override]
    public function save(SongTag $tag): SongTag
    {
        $now = now()->toDateTimeString();

        $this->queryFactory->insert()
            ->into(self::TABLE, ['song_tag_id', 'name', 'order_no', 'created_at', 'updated_at'])
            ->values([
                $this->converter->toBin($tag->songTagId->value),
                $tag->name->value,
                $tag->orderNo->value,
                $now,
                $now,
            ])
            ->build()
            ->append('ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `order_no` = VALUES(`order_no`), `updated_at` = VALUES(`updated_at`)')
            ->execute($this->queryFactory->pdo());

        return $tag;
    }

    #[Override]
    public function isUsed(SongTagId $songTagId): bool
    {
        $count = Row::intValue(
            $this->queryFactory->select()
                ->from('song_taggings')
                ->where('song_tag_id', '=', $this->converter->toBin($songTagId->value))
                ->aggregate($this->queryFactory->pdo(), 'COUNT(*)'),
        );

        return $count > 0;
    }

    #[Override]
    public function delete(SongTagId $songTagId): void
    {
        $this->queryFactory->delete()
            ->from(self::TABLE)
            ->where('song_tag_id', '=', $this->converter->toBin($songTagId->value))
            ->execute($this->queryFactory->pdo());
    }

    #[Override]
    public function getMaxOrderNo(): int
    {
        $max = $this->queryFactory->select()
            ->from(self::TABLE)
            ->aggregate($this->queryFactory->pdo(), 'MAX(order_no)');

        return Row::intValue($max);
    }

    private function applyNameFilter(SelectBuilder $query, SongTagSearchCriteria $criteria): SelectBuilder
    {
        if (! $criteria->name->isPresent()) {
            return $query;
        }

        return $query->where(
            'name_lower',
            'LIKE',
            '%' . SqlHelper::escapeLike(mb_strtolower($criteria->name->get())) . '%',
        );
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrate(array $row): SongTag
    {
        return SongTag::reconstruct(
            $this->converter->toUuid(Row::string($row, 'song_tag_id')),
            Row::string($row, 'name'),
            Row::int($row, 'order_no'),
        );
    }
}
