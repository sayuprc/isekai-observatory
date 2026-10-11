<?php

declare(strict_types=1);

namespace Person\Infrastructures\Admin;

use Emonkak\Orm\SelectBuilder;
use Override;
use Person\Application\Admin\Query\PersonGroupMemberSummary;
use Person\Application\Admin\Query\PersonGroupQueryServiceInterface;
use Person\Application\Admin\Query\PersonGroupSummary;
use Person\Domain\Criteria\PersonGroupSearchCriteria;
use Person\Domain\Models\PersonGroupId;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Infrastructures\Database\QueryFactory;
use Support\Infrastructures\Database\Row;
use Support\Infrastructures\Database\SqlHelper;

readonly class PersonGroupQueryService implements PersonGroupQueryServiceInterface
{
    public function __construct(
        private QueryFactory $queryFactory,
        private UuidConverterInterface $converter,
    ) {
    }

    #[Override]
    public function search(PersonGroupSearchCriteria $criteria): array
    {
        $rows = $this->queryFactory->fetchAll(
            $this->queryFactory->paginate(
                $this->buildSearchQuery($criteria)
                    ->withSelect(['person_group_id', 'name'])
                    ->orderBy('name'),
                $criteria->page,
                $criteria->perPage,
            ),
        );

        return $this->summarize($rows);
    }

    #[Override]
    public function maxPage(PersonGroupSearchCriteria $criteria): int
    {
        return $this->queryFactory->maxPage($this->buildSearchQuery($criteria), $criteria->perPage);
    }

    #[Override]
    public function find(PersonGroupId $personGroupId): ?PersonGroupSummary
    {
        $rows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(['person_group_id', 'name'])
                ->from('person_groups')
                ->where('person_group_id', '=', $this->converter->toBin($personGroupId->value))
                ->limit(1),
        );

        return $this->summarize($rows)[0] ?? null;
    }

    private function buildSearchQuery(PersonGroupSearchCriteria $criteria): SelectBuilder
    {
        $query = $this->queryFactory->select()->from('person_groups');

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
     * @param list<array<string, mixed>> $rows
     *
     * @return list<PersonGroupSummary>
     */
    private function summarize(array $rows): array
    {
        if ($rows === []) {
            return [];
        }

        $memberRows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect([
                    'person_group_members.person_group_id',
                    'person_group_members.person_id',
                    'person_group_members.order_no',
                    'persons.name',
                ])
                ->from('person_group_members')
                ->join('persons', 'persons.person_id = person_group_members.person_id')
                ->where(
                    'person_group_members.person_group_id',
                    'IN',
                    array_map(static fn (array $row): string => Row::string($row, 'person_group_id'), $rows),
                )
                ->orderBy('person_group_members.order_no'),
        );

        $membersByGroup = [];
        foreach ($memberRows as $memberRow) {
            $membersByGroup[Row::string($memberRow, 'person_group_id')][] = new PersonGroupMemberSummary(
                $this->converter->toUuid(Row::string($memberRow, 'person_id')),
                Row::string($memberRow, 'name'),
                Row::int($memberRow, 'order_no'),
            );
        }

        return array_map(
            fn (array $row): PersonGroupSummary => new PersonGroupSummary(
                $this->converter->toUuid(Row::string($row, 'person_group_id')),
                Row::string($row, 'name'),
                $membersByGroup[Row::string($row, 'person_group_id')] ?? [],
            ),
            $rows,
        );
    }
}
