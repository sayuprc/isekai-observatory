<?php

declare(strict_types=1);

namespace Event\Infrastructures\Viewer;

use DateTimeImmutable;
use Emonkak\Orm\SelectBuilder;
use Emonkak\Orm\Sql;
use Event\Application\Viewer\Query\EventCoVocalistSummary;
use Event\Application\Viewer\Query\EventListCursor;
use Event\Application\Viewer\Query\EventListItem;
use Event\Application\Viewer\Query\EventListPage;
use Event\Application\Viewer\Query\EventMediaSummary;
use Event\Application\Viewer\Query\EventPerformanceSummary;
use Event\Application\Viewer\Query\EventQueryServiceInterface;
use Event\Application\Viewer\Query\EventSetlistItemSummary;
use Event\Application\Viewer\Query\EventSourceSummary;
use Event\Application\Viewer\Query\EventVenueSummary;
use Event\Domain\Models\EventStatus;
use Event\Domain\Models\EventType;
use Media\Domain\Models\MediaType;
use Override;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Infrastructures\Database\QueryFactory;
use Support\Infrastructures\Database\Row;
use Venue\Domain\Models\VenueKind;

readonly class EventQueryService implements EventQueryServiceInterface
{
    public function __construct(
        private QueryFactory $queryFactory,
        private UuidConverterInterface $converter,
    ) {
    }

    #[Override]
    public function list(?string $cursor, int $limit): EventListPage
    {
        $query = $this->queryFactory->select()
            ->withSelect(['event_id', 'title', 'description', 'type', 'start_on', 'end_on', 'status'])
            ->from('events')
            ->where('is_display', '=', true);

        if (is_string($cursor)) {
            $query = $this->afterCursor($query, $cursor);
        }

        // MySQL の昇順では日付未定 (NULL) が先頭に来る
        $eventRows = $this->queryFactory->fetchAll(
            $query->orderBy('start_on')
                ->orderBy('event_id')
                ->limit($limit + 1),
        );

        $hasNextPage = count($eventRows) > $limit;
        $pageRows = $hasNextPage ? array_slice($eventRows, 0, $limit) : $eventRows;
        $binEventIds = array_map(static fn (array $row): string => Row::string($row, 'event_id'), $pageRows);

        $venuesByEvent = $this->loadVenues($binEventIds);
        $mediaByEvent = $this->loadMedia($binEventIds);
        $sourcesByEvent = $this->loadSources($binEventIds);
        $performancesByEvent = $this->loadPerformances($binEventIds);
        $setlistByEvent = $this->loadSetlist($binEventIds, $performancesByEvent);

        $events = array_map(
            function (array $row) use ($venuesByEvent, $mediaByEvent, $sourcesByEvent, $performancesByEvent, $setlistByEvent): EventListItem {
                $binEventId = Row::string($row, 'event_id');

                return new EventListItem(
                    $this->converter->toUuid($binEventId),
                    Row::string($row, 'title'),
                    Row::string($row, 'description'),
                    EventType::from(Row::int($row, 'type')),
                    Row::nullableString($row, 'start_on'),
                    Row::nullableString($row, 'end_on'),
                    EventStatus::from(Row::int($row, 'status')),
                    $venuesByEvent[$binEventId] ?? [],
                    $mediaByEvent[$binEventId] ?? [],
                    $sourcesByEvent[$binEventId] ?? [],
                    array_values($performancesByEvent[$binEventId] ?? []),
                    $setlistByEvent[$binEventId] ?? [],
                );
            },
            $pageRows,
        );

        $lastRow = $hasNextPage && $pageRows !== [] ? $pageRows[count($pageRows) - 1] : null;

        return new EventListPage(
            $events,
            $lastRow === null
                ? null
                : EventListCursor::encode(Row::nullableString($lastRow, 'start_on'), $this->converter->toUuid(Row::string($lastRow, 'event_id'))),
        );
    }

    private function afterCursor(SelectBuilder $query, string $cursor): SelectBuilder
    {
        $decoded = EventListCursor::decode($cursor);
        $binEventId = Sql::value($this->converter->toBin($decoded->eventId));

        // キーセットページング: (start_on 昇順 NULL 先頭, event_id 昇順) で cursor より後ろを取る
        if ($decoded->startOn === null) {
            return $query->where(Sql::format('((start_on IS NULL AND event_id > %s) OR start_on IS NOT NULL)', $binEventId));
        }

        return $query->where(Sql::format(
            '(start_on > %s OR (start_on = %s AND event_id > %s))',
            Sql::value($decoded->startOn),
            Sql::value($decoded->startOn),
            $binEventId,
        ));
    }

    /**
     * @param list<string> $binEventIds
     *
     * @return array<string, list<EventVenueSummary>>
     */
    private function loadVenues(array $binEventIds): array
    {
        if ($binEventIds === []) {
            return [];
        }

        $grouped = [];
        foreach ($this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(['event_venues.event_id', 'venues.venue_id', 'venues.name', 'venues.kind'])
                ->from('event_venues')
                ->join('venues', 'event_venues.venue_id = venues.venue_id')
                ->where('event_venues.event_id', 'IN', $binEventIds)
                ->orderBy('event_venues.order_no'),
        ) as $row) {
            $grouped[Row::string($row, 'event_id')][] = new EventVenueSummary(
                $this->converter->toUuid(Row::string($row, 'venue_id')),
                Row::string($row, 'name'),
                VenueKind::from(Row::int($row, 'kind')),
            );
        }

        return $grouped;
    }

    /**
     * @param list<string> $binEventIds
     *
     * @return array<string, list<EventMediaSummary>>
     */
    private function loadMedia(array $binEventIds): array
    {
        if ($binEventIds === []) {
            return [];
        }

        $grouped = [];
        foreach ($this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(['event_media.event_id', 'media.media_id', 'media.title', 'media.url', 'media.published_at', 'media.type'])
                ->from('event_media')
                ->join('media', 'event_media.media_id = media.media_id')
                ->where('event_media.event_id', 'IN', $binEventIds)
                ->where('media.is_display', '=', true)
                ->orderBy('event_media.order_no'),
        ) as $row) {
            $grouped[Row::string($row, 'event_id')][] = new EventMediaSummary(
                $this->converter->toUuid(Row::string($row, 'media_id')),
                Row::string($row, 'title'),
                Row::string($row, 'url'),
                new DateTimeImmutable(Row::string($row, 'published_at')),
                MediaType::from(Row::int($row, 'type')),
            );
        }

        return $grouped;
    }

    /**
     * @param list<string> $binEventIds
     *
     * @return array<string, list<EventSourceSummary>>
     */
    private function loadSources(array $binEventIds): array
    {
        if ($binEventIds === []) {
            return [];
        }

        $grouped = [];
        foreach ($this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(['event_id', 'name', 'url', 'order_no'])
                ->from('event_sources')
                ->where('event_id', 'IN', $binEventIds)
                ->orderBy('order_no'),
        ) as $row) {
            $grouped[Row::string($row, 'event_id')][] = new EventSourceSummary(
                Row::string($row, 'name'),
                Row::string($row, 'url'),
                Row::int($row, 'order_no'),
            );
        }

        return $grouped;
    }

    /**
     * 非公開の楽曲は曲名だけを出し、楽曲 ID を伏せる
     *
     * @param list<string> $binEventIds
     *
     * @return array<string, array<string, EventPerformanceSummary>> イベント ID ごとの、楽曲披露 ID をキーにした一覧
     */
    private function loadPerformances(array $binEventIds): array
    {
        if ($binEventIds === []) {
            return [];
        }

        $rows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(['song_performances.event_id', 'song_performances.performance_id', 'songs.song_id', 'songs.title', 'songs.is_display'])
                ->from('song_performances')
                ->join('songs', 'song_performances.song_id = songs.song_id')
                ->where('song_performances.event_id', 'IN', $binEventIds)
                ->orderBy('song_performances.order_no'),
        );
        $coVocalistsByPerformance = $this->loadCoVocalists(array_map(static fn (array $row): string => Row::string($row, 'performance_id'), $rows));

        $grouped = [];
        foreach ($rows as $row) {
            $binPerformanceId = Row::string($row, 'performance_id');
            $grouped[Row::string($row, 'event_id')][$binPerformanceId] = new EventPerformanceSummary(
                $this->converter->toUuid($binPerformanceId),
                Row::bool($row, 'is_display') ? $this->converter->toUuid(Row::string($row, 'song_id')) : null,
                Row::string($row, 'title'),
                $coVocalistsByPerformance[$binPerformanceId] ?? [],
            );
        }

        return $grouped;
    }

    /**
     * @param list<string> $binPerformanceIds
     *
     * @return array<string, list<EventCoVocalistSummary>>
     */
    private function loadCoVocalists(array $binPerformanceIds): array
    {
        if ($binPerformanceIds === []) {
            return [];
        }

        $grouped = [];
        foreach ($this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(['song_performance_persons.performance_id', 'song_performance_persons.credit_name', 'song_performance_persons.order_no', 'persons.person_id', 'persons.name'])
                ->from('song_performance_persons')
                ->join('persons', 'song_performance_persons.person_id = persons.person_id')
                ->where('song_performance_persons.performance_id', 'IN', $binPerformanceIds)
                ->orderBy('song_performance_persons.order_no'),
        ) as $row) {
            $grouped[Row::string($row, 'performance_id')][] = new EventCoVocalistSummary(
                $this->converter->toUuid(Row::string($row, 'person_id')),
                Row::string($row, 'name'),
                Row::nullableString($row, 'credit_name'),
                Row::int($row, 'order_no'),
            );
        }

        return $grouped;
    }

    /**
     * @param list<string>                                          $binEventIds
     * @param array<string, array<string, EventPerformanceSummary>> $performancesByEvent
     *
     * @return array<string, list<EventSetlistItemSummary>>
     */
    private function loadSetlist(array $binEventIds, array $performancesByEvent): array
    {
        if ($binEventIds === []) {
            return [];
        }

        $itemRows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(['event_id', 'setlist_item_id', 'order_no', 'label'])
                ->from('event_setlist_items')
                ->where('event_id', 'IN', $binEventIds)
                ->orderBy('order_no'),
        );
        $binItemIds = array_map(static fn (array $row): string => Row::string($row, 'setlist_item_id'), $itemRows);

        $performanceIdsByItem = [];
        foreach ($binItemIds === [] ? [] : $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(['setlist_item_id', 'performance_id'])
                ->from('event_setlist_item_performances')
                ->where('setlist_item_id', 'IN', $binItemIds)
                ->orderBy('order_no'),
        ) as $row) {
            $performanceIdsByItem[Row::string($row, 'setlist_item_id')][] = Row::string($row, 'performance_id');
        }

        $grouped = [];
        foreach ($itemRows as $row) {
            $binEventId = Row::string($row, 'event_id');
            $performances = $performancesByEvent[$binEventId] ?? [];
            $grouped[$binEventId][] = new EventSetlistItemSummary(
                Row::int($row, 'order_no'),
                Row::nullableString($row, 'label'),
                array_values(array_filter(array_map(
                    static fn (string $binPerformanceId): ?EventPerformanceSummary => $performances[$binPerformanceId] ?? null,
                    $performanceIdsByItem[Row::string($row, 'setlist_item_id')] ?? [],
                ))),
            );
        }

        return $grouped;
    }
}
