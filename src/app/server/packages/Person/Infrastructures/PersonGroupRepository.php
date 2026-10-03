<?php

declare(strict_types=1);

namespace Person\Infrastructures;

use Override;
use Person\Domain\Models\PersonGroup;
use Person\Domain\Models\PersonGroupId;
use Person\Domain\Models\PersonGroupMembers;
use Person\Domain\Models\PersonGroupName;
use Person\Domain\Models\PersonGroupRepositoryInterface;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Infrastructures\Database\QueryFactory;
use Support\Infrastructures\Database\Row;

readonly class PersonGroupRepository implements PersonGroupRepositoryInterface
{
    private const string TABLE = 'person_groups';

    private const string MEMBER_TABLE = 'person_group_members';

    public function __construct(
        private QueryFactory $queryFactory,
        private UuidConverterInterface $converter,
    ) {
    }

    #[Override]
    public function find(PersonGroupId $personGroupId): ?PersonGroup
    {
        return $this->findBy('person_group_id', [$this->converter->toBin($personGroupId->value)])[0] ?? null;
    }

    #[Override]
    public function findByIds(PersonGroupId ...$personGroupIds): array
    {
        if ($personGroupIds === []) {
            return [];
        }

        return $this->findBy(
            'person_group_id',
            array_values(array_map(fn (PersonGroupId $personGroupId): string => $this->converter->toBin($personGroupId->value), $personGroupIds)),
        );
    }

    #[Override]
    public function findByName(PersonGroupName $name): ?PersonGroup
    {
        return $this->findBy('name', [$name->value])[0] ?? null;
    }

    #[Override]
    public function save(PersonGroup $personGroup): PersonGroup
    {
        $id = $this->converter->toBin($personGroup->personGroupId->value);
        $now = now()->toDateTimeString();

        // メンバーは洗い替えする
        $this->queryFactory->deleteFromTables([self::MEMBER_TABLE], 'person_group_id', $id);

        $this->queryFactory->insert()
            ->into(self::TABLE, ['person_group_id', 'name', 'created_at', 'updated_at'])
            ->values([$id, $personGroup->name->value, $now, $now])
            ->build()
            ->append('ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `updated_at` = VALUES(`updated_at`)')
            ->execute($this->queryFactory->pdo());

        $this->queryFactory->insertRows(
            self::MEMBER_TABLE,
            ['person_group_id', 'person_id', 'order_no'],
            array_map(
                fn (array $member): array => [$id, $this->converter->toBin($member['person_id']), $member['order_no']],
                $personGroup->members->toArray(),
            ),
        );

        return $personGroup;
    }

    #[Override]
    public function delete(PersonGroupId $personGroupId): void
    {
        $this->queryFactory->delete()
            ->from(self::TABLE)
            ->where('person_group_id', '=', $this->converter->toBin($personGroupId->value))
            ->execute($this->queryFactory->pdo());
    }

    /**
     * @param list<string> $values
     *
     * @return list<PersonGroup>
     */
    private function findBy(string $column, array $values): array
    {
        $rows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(['person_group_id', 'name'])
                ->from(self::TABLE)
                ->where($column, 'IN', $values),
        );

        if ($rows === []) {
            return [];
        }

        $memberRows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(['person_group_id', 'person_id', 'order_no'])
                ->from(self::MEMBER_TABLE)
                ->where('person_group_id', 'IN', array_map(static fn (array $row): string => Row::string($row, 'person_group_id'), $rows))
                ->orderBy('order_no'),
        );

        $membersByGroup = [];
        foreach ($memberRows as $memberRow) {
            $membersByGroup[Row::string($memberRow, 'person_group_id')][] = [
                'personId' => $this->converter->toUuid(Row::string($memberRow, 'person_id')),
                'orderNo' => Row::int($memberRow, 'order_no'),
            ];
        }

        return array_map(
            fn (array $row): PersonGroup => new PersonGroup(
                new PersonGroupId($this->converter->toUuid(Row::string($row, 'person_group_id'))),
                new PersonGroupName(Row::string($row, 'name')),
                PersonGroupMembers::reconstruct($membersByGroup[Row::string($row, 'person_group_id')] ?? []),
            ),
            $rows,
        );
    }
}
