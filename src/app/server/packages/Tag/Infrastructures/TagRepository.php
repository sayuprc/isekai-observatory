<?php

declare(strict_types=1);

namespace Tag\Infrastructures;

use Emonkak\Orm\SelectBuilder;
use Override;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Infrastructures\Database\QueryFactory;
use Support\Infrastructures\Database\Row;
use Support\Infrastructures\Database\SqlHelper;
use Tag\Domain\Criteria\TagSearchCriteria;
use Tag\Domain\Models\Tag;
use Tag\Domain\Models\TagId;
use Tag\Domain\Models\TagName;
use Tag\Domain\Models\TagRepositoryInterface;

readonly class TagRepository implements TagRepositoryInterface
{
    private const string TABLE = 'tags';

    /** @var list<string> */
    private const array COLUMNS = ['tag_id', 'name', 'order_no'];

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
    public function search(TagSearchCriteria $criteria): array
    {
        $rows = $this->queryFactory->fetchAll(
            $this->queryFactory->paginate(
                $this->applyNameFilter(
                    $this->queryFactory->select()->withSelect(self::COLUMNS)->from(self::TABLE),
                    $criteria,
                )
                    ->orderBy($criteria->sort->value, $criteria->order->value),
                $criteria->page,
                $criteria->perPage,
            ),
        );

        return array_map($this->hydrate(...), $rows);
    }

    #[Override]
    public function maxPage(TagSearchCriteria $criteria): int
    {
        return $this->queryFactory->maxPage(
            $this->applyNameFilter($this->queryFactory->select()->from(self::TABLE), $criteria),
            $criteria->perPage,
        );
    }

    #[Override]
    public function find(TagId $tagId): ?Tag
    {
        $rows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(self::COLUMNS)
                ->from(self::TABLE)
                ->where('tag_id', '=', $this->converter->toBin($tagId->value))
                ->limit(1),
        );

        $row = $rows[0] ?? null;

        return $row === null ? null : $this->hydrate($row);
    }

    #[Override]
    public function findByName(TagName $name): ?Tag
    {
        $rows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(self::COLUMNS)
                ->from(self::TABLE)
                ->where('name', '=', $name->value)
                ->limit(1),
        );

        $row = $rows[0] ?? null;

        return $row === null ? null : $this->hydrate($row);
    }

    #[Override]
    public function findByIds(TagId ...$tagIds): array
    {
        if ($tagIds === []) {
            return [];
        }

        $binIds = array_map(fn (TagId $tagId): string => $this->converter->toBin($tagId->value), $tagIds);

        $rows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(self::COLUMNS)
                ->from(self::TABLE)
                ->where('tag_id', 'IN', $binIds)
                ->orderBy('order_no'),
        );

        return array_map($this->hydrate(...), $rows);
    }

    #[Override]
    public function save(Tag $tag): Tag
    {
        $now = now()->toDateTimeString();

        $this->queryFactory->insert()
            ->into(self::TABLE, ['tag_id', 'name', 'order_no', 'created_at', 'updated_at'])
            ->values([
                $this->converter->toBin($tag->tagId->value),
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
    public function isUsed(TagId $tagId): bool
    {
        $binTagId = $this->converter->toBin($tagId->value);

        foreach (['song_taggings', 'event_taggings', 'release_group_taggings', 'media_taggings'] as $table) {
            $count = Row::intValue(
                $this->queryFactory->select()
                    ->from($table)
                    ->where('tag_id', '=', $binTagId)
                    ->aggregate($this->queryFactory->pdo(), 'COUNT(*)'),
            );

            if ($count > 0) {
                return true;
            }
        }

        return false;
    }

    #[Override]
    public function delete(TagId $tagId): void
    {
        $this->queryFactory->delete()
            ->from(self::TABLE)
            ->where('tag_id', '=', $this->converter->toBin($tagId->value))
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

    private function applyNameFilter(SelectBuilder $query, TagSearchCriteria $criteria): SelectBuilder
    {
        if (! $criteria->name->isPresent()) {
            return $query;
        }

        return $query->where(
            'name_lower',
            'LIKE',
            SqlHelper::containsPattern(mb_strtolower($criteria->name->get())),
        );
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrate(array $row): Tag
    {
        return Tag::reconstruct(
            $this->converter->toUuid(Row::string($row, 'tag_id')),
            Row::string($row, 'name'),
            Row::int($row, 'order_no'),
        );
    }
}
