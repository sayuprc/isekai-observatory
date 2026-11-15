<?php

declare(strict_types=1);

namespace Event\Infrastructures;

use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Infrastructures\Database\QueryFactory;
use Support\Infrastructures\Database\Row;

/**
 * Event 集約の子テーブルを、複数イベント分まとめて読み込む
 *
 * どのメソッドもバイナリのイベント ID をキーにして返す。子を持たないイベントはキーに含まれない
 *
 * @phpstan-import-type _songPerformanceInput from \Event\Domain\Models\Performances\SongPerformances
 * @phpstan-import-type _setlistItemInput from \Event\Domain\Models\Setlist\Setlist
 * @phpstan-import-type _eventSourceInput from \Event\Domain\Models\Sources\EventSources
 */
readonly class EventChildrenLoader
{
    public function __construct(
        private QueryFactory $queryFactory,
        private UuidConverterInterface $converter,
    ) {
    }

    /**
     * 行をイベント ID ごとにまとめ、各行を集約へ渡す形に変換する
     *
     * @template T
     *
     * @param list<array<string, mixed>>        $rows
     * @param callable(array<string, mixed>): T $map
     *
     * @return array<string, list<T>>
     */
    private function groupByEventId(array $rows, callable $map): array
    {
        $grouped = [];
        foreach ($rows as $row) {
            $grouped[Row::string($row, 'event_id')][] = $map($row);
        }

        return $grouped;
    }

    /**
     * @param list<string> $binEventIds
     *
     * @return array<string, list<array{venueId: string, orderNo: int}>>
     */
    public function loadVenues(array $binEventIds): array
    {
        $rows = $this->queryFactory
            ->select()
            ->withSelect(['event_id', 'venue_id', 'order_no'])
            ->from('event_venues')
            ->where('event_id', 'IN', $binEventIds)
            ->orderBy('order_no')
            |> $this->queryFactory->fetchAll(...);

        return $this->groupByEventId(
            $rows,
            fn (array $row): array => [
                'venueId' => $this->converter->toUuid(Row::string($row, 'venue_id')),
                'orderNo' => Row::int($row, 'order_no'),
            ],
        );
    }

    /**
     * @param list<string> $binEventIds
     *
     * @return array<string, list<array{tagId: string}>>
     */
    public function loadTags(array $binEventIds): array
    {
        $rows = $this->queryFactory
            ->select()
            ->withSelect(['event_taggings.event_id', 'event_taggings.tag_id'])
            ->from('event_taggings')
            ->join('tags', 'event_taggings.tag_id = tags.tag_id')
            ->where('event_taggings.event_id', 'IN', $binEventIds)
            ->orderBy('tags.order_no')
            |> $this->queryFactory->fetchAll(...);

        return $this->groupByEventId(
            $rows,
            fn (array $row): array => ['tagId' => $this->converter->toUuid(Row::string($row, 'tag_id'))],
        );
    }

    /**
     * @param list<string> $binEventIds
     *
     * @return array<string, list<array{mediaId: string, orderNo: int}>>
     */
    public function loadMedia(array $binEventIds): array
    {
        $rows = $this->queryFactory
            ->select()
            ->withSelect(['event_id', 'media_id', 'order_no'])
            ->from('event_media')
            ->where('event_id', 'IN', $binEventIds)
            ->orderBy('order_no')
            |> $this->queryFactory->fetchAll(...);

        return $this->groupByEventId(
            $rows,
            fn (array $row): array => [
                'mediaId' => $this->converter->toUuid(Row::string($row, 'media_id')),
                'orderNo' => Row::int($row, 'order_no'),
            ],
        );
    }

    /**
     * @param list<string> $binEventIds
     *
     * @return array<string, list<array{releaseId: string, orderNo: int}>>
     */
    public function loadReleases(array $binEventIds): array
    {
        $rows = $this->queryFactory
            ->select()
            ->withSelect(['event_id', 'release_id', 'order_no'])
            ->from('event_releases')
            ->where('event_id', 'IN', $binEventIds)
            ->orderBy('order_no')
            |> $this->queryFactory->fetchAll(...);

        return $this->groupByEventId(
            $rows,
            fn (array $row): array => [
                'releaseId' => $this->converter->toUuid(Row::string($row, 'release_id')),
                'orderNo' => Row::int($row, 'order_no'),
            ],
        );
    }

    /**
     * @param list<string> $binEventIds
     *
     * @return array<string, list<_eventSourceInput>>
     */
    public function loadSources(array $binEventIds): array
    {
        $rows = $this->queryFactory
            ->select()
            ->withSelect(['event_id', 'name', 'url', 'order_no'])
            ->from('event_sources')
            ->where('event_id', 'IN', $binEventIds)
            ->orderBy('order_no')
            |> $this->queryFactory->fetchAll(...);

        return $this->groupByEventId(
            $rows,
            static fn (array $row): array => [
                'displayName' => Row::string($row, 'name'),
                'url' => Row::string($row, 'url'),
                'orderNo' => Row::int($row, 'order_no'),
            ],
        );
    }

    /**
     * @param list<string> $binEventIds
     *
     * @return array<string, list<_songPerformanceInput>>
     */
    public function loadPerformances(array $binEventIds): array
    {
        $performanceRows = $this->queryFactory
            ->select()
            ->withSelect(['event_id', 'performance_id', 'song_id', 'order_no'])
            ->from('song_performances')
            ->where('event_id', 'IN', $binEventIds)
            ->orderBy('order_no')
            |> $this->queryFactory->fetchAll(...);

        $personRows = $this->queryFactory
            ->select()
            ->withSelect([
                'song_performance_persons.performance_id',
                'song_performance_persons.person_id',
                'song_performance_persons.order_no',
                'song_performance_persons.credit_name',
                'song_performance_persons.person_group_id',
            ])
            ->from('song_performance_persons')
            ->join(
                'song_performances',
                'song_performances.performance_id = song_performance_persons.performance_id',
            )
            ->where('song_performances.event_id', 'IN', $binEventIds)
            ->orderBy('song_performance_persons.order_no')
            |> $this->queryFactory->fetchAll(...);

        $coVocalistsByPerformance = [];
        foreach ($personRows as $row) {
            $coVocalistsByPerformance[Row::string($row, 'performance_id')][] = [
                'personId' => $this->converter->toUuid(Row::string($row, 'person_id')),
                'creditName' => Row::nullableString($row, 'credit_name'),
                'personGroupId' => ($binGroupId = Row::nullableString($row, 'person_group_id')) === null
                    ? null
                    : $this->converter->toUuid($binGroupId),
                'orderNo' => Row::int($row, 'order_no'),
            ];
        }

        return $this->groupByEventId(
            $performanceRows,
            fn (array $row): array => [
                'performanceId' => $this->converter->toUuid(Row::string($row, 'performance_id')),
                'songId' => $this->converter->toUuid(Row::string($row, 'song_id')),
                'orderNo' => Row::int($row, 'order_no'),
                'coVocalists' => $coVocalistsByPerformance[Row::string($row, 'performance_id')] ?? [],
            ],
        );
    }

    /**
     * @param list<string> $binEventIds
     *
     * @return array<string, list<_setlistItemInput>>
     */
    public function loadSetlist(array $binEventIds): array
    {
        $itemRows = $this->queryFactory
            ->select()
            ->withSelect(['event_id', 'setlist_item_id', 'order_no', 'label'])
            ->from('event_setlist_items')
            ->where('event_id', 'IN', $binEventIds)
            ->orderBy('order_no')
            |> $this->queryFactory->fetchAll(...);

        $linkRows = $this->queryFactory
            ->select()
            ->withSelect([
                'event_setlist_item_performances.setlist_item_id',
                'event_setlist_item_performances.performance_id',
            ])
            ->from('event_setlist_item_performances')
            ->join(
                'event_setlist_items',
                'event_setlist_items.setlist_item_id = event_setlist_item_performances.setlist_item_id',
            )
            ->where('event_setlist_items.event_id', 'IN', $binEventIds)
            ->orderBy('event_setlist_item_performances.order_no')
            |> $this->queryFactory->fetchAll(...);

        $performanceIdsByItem = [];
        foreach ($linkRows as $row) {
            $setlistItemId = Row::string($row, 'setlist_item_id');
            $performanceIdsByItem[$setlistItemId][] = $this->converter->toUuid(Row::string($row, 'performance_id'));
        }

        return $this->groupByEventId(
            $itemRows,
            fn (array $row): array => [
                'setlistItemId' => $this->converter->toUuid(Row::string($row, 'setlist_item_id')),
                'orderNo' => Row::int($row, 'order_no'),
                'label' => Row::nullableString($row, 'label'),
                'performanceIds' => $performanceIdsByItem[Row::string($row, 'setlist_item_id')] ?? [],
            ],
        );
    }
}
