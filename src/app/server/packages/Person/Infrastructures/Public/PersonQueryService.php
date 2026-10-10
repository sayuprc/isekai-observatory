<?php

declare(strict_types=1);

namespace Person\Infrastructures\Public;

use Emonkak\Orm\Sql;
use Override;
use Person\Application\Public\Query\PersonListItem;
use Person\Application\Public\Query\PersonListPage;
use Person\Application\Public\Query\PersonListPageToken;
use Person\Application\Public\Query\PersonQueryServiceInterface;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Infrastructures\Database\QueryFactory;
use Support\Infrastructures\Database\Row;

readonly class PersonQueryService implements PersonQueryServiceInterface
{
    public function __construct(
        private QueryFactory $queryFactory,
        private UuidConverterInterface $converter,
    ) {
    }

    #[Override]
    public function list(?string $pageToken, int $pageSize): PersonListPage
    {
        $query = $this->queryFactory->select()
            ->withSelect(['person_id', 'name', 'order_no'])
            ->from('persons')
            ->where(new Sql('person_id IN (' . PublicReferenceSql::PERSON_IDS . ')'));

        if (is_string($pageToken)) {
            $decoded = PersonListPageToken::decode($pageToken);

            $query = $query->where(Sql::format(
                '(order_no > %s OR (order_no = %s AND person_id > %s))',
                Sql::value($decoded->orderNo),
                Sql::value($decoded->orderNo),
                Sql::value($this->converter->toBin($decoded->personId)),
            ));
        }

        $rows = $this->queryFactory->fetchAll(
            $query->orderBy('order_no')
                ->orderBy('person_id')
                ->limit($pageSize + 1),
        );

        $hasNextPage = count($rows) > $pageSize;
        $pageRows = $hasNextPage ? array_slice($rows, 0, $pageSize) : $rows;

        $persons = array_map(
            fn (array $row): PersonListItem => new PersonListItem(
                $this->converter->toUuid(Row::string($row, 'person_id')),
                Row::string($row, 'name'),
            ),
            $pageRows,
        );

        $lastRow = $hasNextPage && $pageRows !== [] ? $pageRows[count($pageRows) - 1] : null;

        return new PersonListPage(
            $persons,
            $lastRow === null
                ? null
                : PersonListPageToken::encode(Row::int($lastRow, 'order_no'), $this->converter->toUuid(Row::string($lastRow, 'person_id'))),
        );
    }
}
