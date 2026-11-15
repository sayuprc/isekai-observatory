<?php

declare(strict_types=1);

namespace Person\Infrastructures\Public;

use Emonkak\Orm\Sql;
use Override;
use Person\Application\Public\Query\PersonGroupListItem;
use Person\Application\Public\Query\PersonGroupListPage;
use Person\Application\Public\Query\PersonGroupListPageToken;
use Person\Application\Public\Query\PersonGroupQueryServiceInterface;
use SensitiveParameter;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Infrastructures\Database\QueryFactory;
use Support\Infrastructures\Database\Row;

readonly class PersonGroupQueryService implements PersonGroupQueryServiceInterface
{
    public function __construct(
        private QueryFactory $queryFactory,
        private UuidConverterInterface $converter,
    ) {
    }

    #[Override]
    public function list(#[SensitiveParameter] ?string $pageToken, int $pageSize): PersonGroupListPage
    {
        $query = $this->queryFactory
            ->select()
            ->withSelect(['person_group_id', 'display_name'])
            ->from('person_groups')
            ->where(new Sql('person_group_id IN (' . PublicReferenceSql::PERSON_GROUP_IDS . ')'));

        if (is_string($pageToken)) {
            $decoded = PersonGroupListPageToken::decode($pageToken);

            $query = Sql::format(
                '(display_name > %s OR (display_name = %s AND person_group_id > %s))',
                Sql::value($decoded->name),
                Sql::value($decoded->name),
                Sql::value($this->converter->toBin($decoded->personGroupId)),
            )
                |> $query->where(...);
        }

        $rows = $query
            ->orderBy('display_name')
            ->orderBy('person_group_id')
            ->limit($pageSize + 1)
            |> $this->queryFactory->fetchAll(...);

        $hasNextPage = count($rows) > $pageSize;
        $pageRows = $hasNextPage ? array_slice($rows, 0, $pageSize) : $rows;
        $memberIdsByGroup = array_map(static fn (array $row): string => Row::string($row, 'person_group_id'), $pageRows)
            |> $this->loadMemberIds(...);

        $personGroups = array_map(
            fn (array $row): PersonGroupListItem => new PersonGroupListItem(
                $this->converter->toUuid(Row::string($row, 'person_group_id')),
                Row::string($row, 'display_name'),
                $memberIdsByGroup[Row::string($row, 'person_group_id')] ?? [],
            ),
            $pageRows,
        );

        $lastRow = $hasNextPage && $pageRows !== [] ? $pageRows[count($pageRows) - 1] : null;

        return new PersonGroupListPage(
            $personGroups,
            $lastRow === null
                ? null
                : PersonGroupListPageToken::encode(
                    Row::string($lastRow, 'display_name'),
                    $this->converter->toUuid(Row::string($lastRow, 'person_group_id')),
                ),
        );
    }

    /**
     * @param list<string> $binGroupIds
     *
     * @return array<string, list<string>>
     */
    private function loadMemberIds(array $binGroupIds): array
    {
        if ($binGroupIds === []) {
            return [];
        }

        $rows = $this->queryFactory
            ->select()
            ->withSelect(['person_group_id', 'person_id'])
            ->from('person_group_members')
            ->where('person_group_id', 'IN', $binGroupIds)
            ->orderBy('order_no')
            |> $this->queryFactory->fetchAll(...);

        $grouped = [];
        foreach ($rows as $row) {
            $grouped[Row::string($row, 'person_group_id')][] = $this->converter->toUuid(Row::string($row, 'person_id'));
        }

        return $grouped;
    }
}
