<?php

declare(strict_types=1);

namespace Venue\Infrastructures\Public;

use Emonkak\Orm\Sql;
use Override;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Infrastructures\Database\QueryFactory;
use Support\Infrastructures\Database\Row;
use Venue\Application\Public\Query\VenueListItem;
use Venue\Application\Public\Query\VenueListPage;
use Venue\Application\Public\Query\VenueListPageToken;
use Venue\Application\Public\Query\VenueQueryServiceInterface;
use Venue\Domain\Models\VenueKind;

readonly class VenueQueryService implements VenueQueryServiceInterface
{
    public function __construct(
        private QueryFactory $queryFactory,
        private UuidConverterInterface $converter,
    ) {
    }

    /**
     * 公開イベントから参照されている開催先だけを返す
     */
    #[Override]
    public function list(?string $pageToken, int $pageSize): VenueListPage
    {
        $query = $this->queryFactory->select()
            ->withSelect(['venue_id', 'name', 'kind'])
            ->from('venues')
            ->where(new Sql(
                'venue_id IN (SELECT event_venues.venue_id FROM event_venues'
                . ' INNER JOIN events ON events.event_id = event_venues.event_id WHERE events.is_display = TRUE)',
            ));

        if (is_string($pageToken)) {
            $decoded = VenueListPageToken::decode($pageToken);

            $query = $query->where(Sql::format(
                '(name > %s OR (name = %s AND venue_id > %s))',
                Sql::value($decoded->name),
                Sql::value($decoded->name),
                Sql::value($this->converter->toBin($decoded->venueId)),
            ));
        }

        $rows = $this->queryFactory->fetchAll(
            $query->orderBy('name')
                ->orderBy('venue_id')
                ->limit($pageSize + 1),
        );

        $hasNextPage = count($rows) > $pageSize;
        $pageRows = $hasNextPage ? array_slice($rows, 0, $pageSize) : $rows;

        $venues = array_map(
            fn (array $row): VenueListItem => new VenueListItem(
                $this->converter->toUuid(Row::string($row, 'venue_id')),
                Row::string($row, 'name'),
                VenueKind::from(Row::int($row, 'kind')),
            ),
            $pageRows,
        );

        $lastRow = $hasNextPage && $pageRows !== [] ? $pageRows[count($pageRows) - 1] : null;

        return new VenueListPage(
            $venues,
            $lastRow === null
                ? null
                : VenueListPageToken::encode(Row::string($lastRow, 'name'), $this->converter->toUuid(Row::string($lastRow, 'venue_id'))),
        );
    }
}
