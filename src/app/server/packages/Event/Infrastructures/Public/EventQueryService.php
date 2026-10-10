<?php

declare(strict_types=1);

namespace Event\Infrastructures\Public;

use Emonkak\Orm\Sql;
use Event\Application\Public\Query\CoVocalistItem;
use Event\Application\Public\Query\EventListItem;
use Event\Application\Public\Query\EventListPage;
use Event\Application\Public\Query\EventListPageToken;
use Event\Application\Public\Query\EventQueryServiceInterface;
use Event\Application\Public\Query\EventSourceItem;
use Event\Application\Public\Query\PerformanceItem;
use Event\Application\Public\Query\SetlistItem;
use Event\Domain\Models\EventStatus;
use Event\Domain\Models\EventType;
use Override;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Infrastructures\Database\QueryFactory;
use Support\Infrastructures\Database\Row;

readonly class EventQueryService implements EventQueryServiceInterface
{
    public function __construct(
        private QueryFactory $queryFactory,
        private UuidConverterInterface $converter,
    ) {
    }

    #[Override]
    public function list(?string $pageToken, int $pageSize): EventListPage
    {
        $query = $this->queryFactory->select()
            ->withSelect(['event_id', 'title', 'description', 'type', 'start_on', 'end_on', 'status'])
            ->from('events')
            ->where('is_display', '=', true);

        if (is_string($pageToken)) {
            $decoded = EventListPageToken::decode($pageToken);

            $query = $query->where(Sql::format(
                '(title > %s OR (title = %s AND event_id > %s))',
                Sql::value($decoded->title),
                Sql::value($decoded->title),
                Sql::value($this->converter->toBin($decoded->eventId)),
            ));
        }

        $eventRows = $this->queryFactory->fetchAll(
            $query->orderBy('title')
                ->orderBy('event_id')
                ->limit($pageSize + 1),
        );

        $hasNextPage = count($eventRows) > $pageSize;
        $pageRows = $hasNextPage ? array_slice($eventRows, 0, $pageSize) : $eventRows;
        $binEventIds = array_map(static fn (array $row): string => Row::string($row, 'event_id'), $pageRows);

        $venueIdsByEvent = $this->loadIds($binEventIds, 'event_venues', 'venue_id');
        $mediaIdsByEvent = $this->loadMediaIds($binEventIds);
        $releaseIdsByEvent = $this->loadReleaseIds($binEventIds);
        $sourcesByEvent = $this->loadSources($binEventIds);
        $performancesByEvent = $this->loadPerformances($binEventIds);
        $setlistByEvent = $this->loadSetlist($binEventIds);

        $events = array_map(
            function (array $row) use ($venueIdsByEvent, $mediaIdsByEvent, $releaseIdsByEvent, $sourcesByEvent, $performancesByEvent, $setlistByEvent): EventListItem {
                $binEventId = Row::string($row, 'event_id');

                return new EventListItem(
                    $this->converter->toUuid($binEventId),
                    Row::string($row, 'title'),
                    Row::string($row, 'description'),
                    EventType::from(Row::int($row, 'type')),
                    Row::nullableString($row, 'start_on'),
                    Row::nullableString($row, 'end_on'),
                    EventStatus::from(Row::int($row, 'status')),
                    $venueIdsByEvent[$binEventId] ?? [],
                    $mediaIdsByEvent[$binEventId] ?? [],
                    $releaseIdsByEvent[$binEventId] ?? [],
                    $sourcesByEvent[$binEventId] ?? [],
                    $performancesByEvent[$binEventId] ?? [],
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
                : EventListPageToken::encode(Row::string($lastRow, 'title'), $this->converter->toUuid(Row::string($lastRow, 'event_id'))),
        );
    }

    /**
     * @param list<string> $binEventIds
     *
     * @return array<string, list<string>>
     */
    private function loadIds(array $binEventIds, string $table, string $idColumn): array
    {
        if ($binEventIds === []) {
            return [];
        }

        $grouped = [];
        foreach ($this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(['event_id', $idColumn])
                ->from($table)
                ->where('event_id', 'IN', $binEventIds)
                ->orderBy('order_no'),
        ) as $row) {
            $grouped[Row::string($row, 'event_id')][] = $this->converter->toUuid(Row::string($row, $idColumn));
        }

        return $grouped;
    }

    /**
     * @param list<string> $binEventIds
     *
     * @return array<string, list<string>>
     */
    private function loadMediaIds(array $binEventIds): array
    {
        if ($binEventIds === []) {
            return [];
        }

        $grouped = [];
        foreach ($this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(['event_media.event_id', 'media.media_id'])
                ->from('event_media')
                ->join('media', 'event_media.media_id = media.media_id')
                ->where('event_media.event_id', 'IN', $binEventIds)
                ->where('media.is_display', '=', true)
                ->orderBy('event_media.order_no'),
        ) as $row) {
            $grouped[Row::string($row, 'event_id')][] = $this->converter->toUuid(Row::string($row, 'media_id'));
        }

        return $grouped;
    }

    /**
     * リリースとリリースグループがともに公開のものだけを返す
     *
     * @param list<string> $binEventIds
     *
     * @return array<string, list<string>>
     */
    private function loadReleaseIds(array $binEventIds): array
    {
        if ($binEventIds === []) {
            return [];
        }

        $grouped = [];
        foreach ($this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(['event_releases.event_id', 'releases.release_id'])
                ->from('event_releases')
                ->join('releases', 'event_releases.release_id = releases.release_id')
                ->join('release_groups', 'releases.release_group_id = release_groups.release_group_id')
                ->where('event_releases.event_id', 'IN', $binEventIds)
                ->where('releases.is_display', '=', true)
                ->where('release_groups.is_display', '=', true)
                ->orderBy('event_releases.order_no'),
        ) as $row) {
            $grouped[Row::string($row, 'event_id')][] = $this->converter->toUuid(Row::string($row, 'release_id'));
        }

        return $grouped;
    }

    /**
     * @param list<string> $binEventIds
     *
     * @return array<string, list<EventSourceItem>>
     */
    private function loadSources(array $binEventIds): array
    {
        if ($binEventIds === []) {
            return [];
        }

        $grouped = [];
        foreach ($this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(['event_id', 'name', 'url'])
                ->from('event_sources')
                ->where('event_id', 'IN', $binEventIds)
                ->orderBy('order_no'),
        ) as $row) {
            $grouped[Row::string($row, 'event_id')][] = new EventSourceItem(Row::string($row, 'name'), Row::string($row, 'url'));
        }

        return $grouped;
    }

    /**
     * 非公開の楽曲は曲名だけを出し、楽曲 ID を伏せる
     *
     * @param list<string> $binEventIds
     *
     * @return array<string, list<PerformanceItem>>
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
            $grouped[Row::string($row, 'event_id')][] = new PerformanceItem(
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
     * @return array<string, list<CoVocalistItem>>
     */
    private function loadCoVocalists(array $binPerformanceIds): array
    {
        if ($binPerformanceIds === []) {
            return [];
        }

        $grouped = [];
        foreach ($this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(['performance_id', 'person_id', 'credit_name', 'person_group_id'])
                ->from('song_performance_persons')
                ->where('performance_id', 'IN', $binPerformanceIds)
                ->orderBy('order_no'),
        ) as $row) {
            $binGroupId = Row::nullableString($row, 'person_group_id');
            $grouped[Row::string($row, 'performance_id')][] = new CoVocalistItem(
                $this->converter->toUuid(Row::string($row, 'person_id')),
                Row::nullableString($row, 'credit_name'),
                $binGroupId === null ? null : $this->converter->toUuid($binGroupId),
            );
        }

        return $grouped;
    }

    /**
     * @param list<string> $binEventIds
     *
     * @return array<string, list<SetlistItem>>
     */
    private function loadSetlist(array $binEventIds): array
    {
        if ($binEventIds === []) {
            return [];
        }

        $itemRows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(['event_id', 'setlist_item_id', 'label'])
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
            $performanceIdsByItem[Row::string($row, 'setlist_item_id')][] = $this->converter->toUuid(Row::string($row, 'performance_id'));
        }

        $grouped = [];
        foreach ($itemRows as $row) {
            $grouped[Row::string($row, 'event_id')][] = new SetlistItem(
                Row::nullableString($row, 'label'),
                $performanceIdsByItem[Row::string($row, 'setlist_item_id')] ?? [],
            );
        }

        return $grouped;
    }
}
