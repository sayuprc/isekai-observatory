<?php

declare(strict_types=1);

namespace Event\Infrastructures\Admin;

use Emonkak\Orm\SelectBuilder;
use Emonkak\Orm\Sql;
use Event\Application\Admin\Query\EventSearchQueryServiceInterface;
use Event\Application\Admin\Query\EventSummary;
use Event\Domain\Criteria\EventSearchCriteria;
use Event\Domain\Criteria\Sort;
use Event\Domain\Models\EventStatus;
use Event\Domain\Models\EventType;
use Override;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Infrastructures\Database\QueryFactory;
use Support\Infrastructures\Database\Row;
use Support\Infrastructures\Database\SqlHelper;

readonly class EventSearchQueryService implements EventSearchQueryServiceInterface
{
    public function __construct(
        private QueryFactory $queryFactory,
        private UuidConverterInterface $converter,
    ) {
    }

    #[Override]
    public function search(EventSearchCriteria $criteria): array
    {
        $sort = match ($criteria->sort) {
            Sort::Schedule => 'start_on',
            Sort::Title => 'title_lower',
        };

        $rows = $this->queryFactory->fetchAll(
            $this->queryFactory->paginate(
                $this->buildSearchQuery($criteria)
                    ->withSelect(['event_id', 'title', 'type', 'start_on', 'end_on', 'status', 'is_display'])
                    ->orderBy($sort, $criteria->order->value)
                    ->orderBy('event_id'),
                $criteria->page,
                $criteria->perPage,
            ),
        );

        $binEventIds = array_map(static fn (array $row): string => Row::string($row, 'event_id'), $rows);
        $venueNames = $this->loadVenueNames($binEventIds);
        $performanceCounts = $this->countByEvent('song_performances', $binEventIds);
        $setlistItemCounts = $this->countByEvent('event_setlist_items', $binEventIds);
        $sourceCounts = $this->countByEvent('event_sources', $binEventIds);

        return array_map(
            fn (array $row): EventSummary => new EventSummary(
                $this->converter->toUuid(Row::string($row, 'event_id')),
                Row::string($row, 'title'),
                EventType::from(Row::int($row, 'type')),
                Row::nullableString($row, 'start_on'),
                Row::nullableString($row, 'end_on'),
                EventStatus::from(Row::int($row, 'status')),
                Row::bool($row, 'is_display'),
                $venueNames[Row::string($row, 'event_id')] ?? [],
                $performanceCounts[Row::string($row, 'event_id')] ?? 0,
                $setlistItemCounts[Row::string($row, 'event_id')] ?? 0,
                $sourceCounts[Row::string($row, 'event_id')] ?? 0,
            ),
            $rows,
        );
    }

    #[Override]
    public function maxPage(EventSearchCriteria $criteria): int
    {
        return $this->queryFactory->maxPage($this->buildSearchQuery($criteria), $criteria->perPage);
    }

    /**
     * 一覧の 1 ページ分の開催先名を、行ごとに問い合わせず 1 回で引く
     *
     * @param list<string> $binEventIds
     *
     * @return array<string, list<string>>
     */
    private function loadVenueNames(array $binEventIds): array
    {
        if ($binEventIds === []) {
            return [];
        }

        $grouped = [];
        foreach ($this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(['event_venues.event_id', 'venues.name'])
                ->from('event_venues')
                ->join('venues', 'event_venues.venue_id = venues.venue_id')
                ->where('event_venues.event_id', 'IN', $binEventIds)
                ->orderBy('event_venues.order_no'),
        ) as $row) {
            $grouped[Row::string($row, 'event_id')][] = Row::string($row, 'name');
        }

        return $grouped;
    }

    /**
     * 子テーブルの件数をイベントごとに数える。行のないイベントは結果に含まれない
     *
     * @param 'event_setlist_items'|'event_sources'|'song_performances' $table
     * @param list<string>                                               $binEventIds
     *
     * @return array<string, int>
     */
    private function countByEvent(string $table, array $binEventIds): array
    {
        if ($binEventIds === []) {
            return [];
        }

        $counts = [];
        foreach ($this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(['event_id'])
                ->select(new Sql('COUNT(*)'), 'count')
                ->from($table)
                ->where('event_id', 'IN', $binEventIds)
                ->groupBy('event_id'),
        ) as $row) {
            $counts[Row::string($row, 'event_id')] = Row::int($row, 'count');
        }

        return $counts;
    }

    private function buildSearchQuery(EventSearchCriteria $criteria): SelectBuilder
    {
        $query = $this->queryFactory->select()->from('events');

        if ($criteria->title->isPresent()) {
            $query = $query->where(
                'title_lower',
                'LIKE',
                SqlHelper::containsPattern(mb_strtolower($criteria->title->get())),
            );
        }

        if ($criteria->type->isPresent()) {
            $query = $query->where('type', '=', $criteria->type->get()->value);
        }

        if ($criteria->status->isPresent()) {
            $query = $query->where('status', '=', $criteria->status->get()->value);
        }

        if ($criteria->isDisplay->isPresent()) {
            $query = $query->where('is_display', '=', $criteria->isDisplay->get());
        }

        return $query;
    }
}
