<?php

declare(strict_types=1);

namespace Person\Infrastructures;

use Emonkak\Orm\SelectBuilder;
use Override;
use Person\Domain\Criteria\PersonSearchCriteria;
use Person\Domain\Models\Person;
use Person\Domain\Models\PersonId;
use Person\Domain\Models\PersonName;
use Person\Domain\Models\PersonRepositoryInterface;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Infrastructures\Database\QueryFactory;
use Support\Infrastructures\Database\Row;
use Support\Infrastructures\Database\SqlHelper;

readonly class PersonRepository implements PersonRepositoryInterface
{
    private const string TABLE = 'persons';

    /** @var list<string> */
    private const array COLUMNS = ['person_id', 'name', 'order_no'];

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
    public function search(PersonSearchCriteria $criteria): array
    {
        $query = $this->applyNameFilter(
            $this->queryFactory->select()->withSelect(self::COLUMNS)->from(self::TABLE),
            $criteria,
        );

        $offset = ($criteria->page - 1) * $criteria->perPage->value;

        $rows = $this->queryFactory->fetchAll(
            $query
                ->orderBy($criteria->sort->value, $criteria->order->value)
                ->limit($criteria->perPage->value)
                ->offset($offset),
        );

        return array_map($this->hydrate(...), $rows);
    }

    #[Override]
    public function maxPage(PersonSearchCriteria $criteria): int
    {
        $query = $this->applyNameFilter(
            $this->queryFactory->select()->from(self::TABLE),
            $criteria,
        );

        $count = Row::intValue($query->aggregate($this->queryFactory->pdo(), 'COUNT(*)'));

        return (int)ceil($count / $criteria->perPage->value);
    }

    #[Override]
    public function find(PersonId $personId): ?Person
    {
        $rows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(self::COLUMNS)
                ->from(self::TABLE)
                ->where('person_id', '=', $this->converter->toBin($personId->value))
                ->limit(1),
        );

        $row = $rows[0] ?? null;

        return is_null($row) ? null : $this->hydrate($row);
    }

    #[Override]
    public function findByIds(PersonId ...$personIds): array
    {
        if ($personIds === []) {
            return [];
        }

        $binIds = array_map(fn (PersonId $personId): string => $this->converter->toBin($personId->value), $personIds);

        $rows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(self::COLUMNS)
                ->from(self::TABLE)
                ->where('person_id', 'IN', $binIds),
        );

        return array_map($this->hydrate(...), $rows);
    }

    #[Override]
    public function findByName(PersonName $name): ?Person
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
    public function save(Person $person): Person
    {
        $now = now()->toDateTimeString();

        // emonkak のビルダは upsert を直接表現できないため、INSERT に
        // ON DUPLICATE KEY UPDATE を付与する。VALUES(col) で挿入値を再利用し追加バインドを避ける
        $this->queryFactory->insert()
            ->into(self::TABLE, ['person_id', 'name', 'order_no', 'created_at', 'updated_at'])
            ->values([
                $this->converter->toBin($person->personId->value),
                $person->name->value,
                $person->orderNo->value,
                $now,
                $now,
            ])
            ->build()
            ->append('ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `order_no` = VALUES(`order_no`), `updated_at` = VALUES(`updated_at`)')
            ->execute($this->queryFactory->pdo());

        return $person;
    }

    #[Override]
    public function delete(PersonId $personId): void
    {
        $this->queryFactory->delete()
            ->from(self::TABLE)
            ->where('person_id', '=', $this->converter->toBin($personId->value))
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

    private function applyNameFilter(SelectBuilder $query, PersonSearchCriteria $criteria): SelectBuilder
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
    private function hydrate(array $row): Person
    {
        return Person::reconstruct(
            $this->converter->toUuid(Row::string($row, 'person_id')),
            Row::string($row, 'name'),
            Row::int($row, 'order_no'),
        );
    }
}
