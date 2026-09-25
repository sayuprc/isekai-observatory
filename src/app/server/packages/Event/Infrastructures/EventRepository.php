<?php

declare(strict_types=1);

namespace Event\Infrastructures;

use Emonkak\Database\PDOInterface;
use Event\Domain\Models\Event;
use Event\Domain\Models\EventId;
use Event\Domain\Models\EventRepositoryInterface;
use Override;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Infrastructures\Database\QueryFactory;
use Support\Infrastructures\Database\Row;

/**
 * @phpstan-import-type _songPerformanceInput from \Event\Domain\Models\Performances\SongPerformances
 * @phpstan-import-type _setlistItemInput from \Event\Domain\Models\Setlist\Setlist
 * @phpstan-import-type _eventSourceInput from \Event\Domain\Models\Sources\EventSources
 */
readonly class EventRepository implements EventRepositoryInterface
{
    private const string TABLE = 'events';

    /** @var list<string> */
    private const array COLUMNS = [
        'event_id',
        'title',
        'description',
        'type',
        'start_on',
        'end_on',
        'status',
        'is_display',
    ];

    public function __construct(
        private QueryFactory $queryFactory,
        private UuidConverterInterface $converter,
    ) {
    }

    #[Override]
    public function find(EventId $eventId): ?Event
    {
        $rows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(self::COLUMNS)
                ->from(self::TABLE)
                ->where('event_id', '=', $this->converter->toBin($eventId->value))
                ->limit(1),
        );

        return $rows === [] ? null : $this->hydrate($rows[0]);
    }

    #[Override]
    public function save(Event $event): Event
    {
        $pdo = $this->queryFactory->pdo();
        $binEventId = $this->converter->toBin($event->eventId->value);
        $now = now()->toDateTimeString();
        $data = $event->toArray();

        // 子テーブルは洗い替えする
        $this->deleteChildren($pdo, $binEventId);

        $this->queryFactory->insert()
            ->into(self::TABLE, [...self::COLUMNS, 'created_at', 'updated_at'])
            ->values([
                $binEventId,
                $data['title'],
                $data['description'],
                $data['type'],
                $data['start_on'],
                $data['end_on'],
                $data['status'],
                $data['is_display'],
                $now,
                $now,
            ])
            ->build()
            ->append(
                'ON DUPLICATE KEY UPDATE '
                . '`title` = VALUES(`title`), '
                . '`description` = VALUES(`description`), '
                . '`type` = VALUES(`type`), '
                . '`start_on` = VALUES(`start_on`), '
                . '`end_on` = VALUES(`end_on`), '
                . '`status` = VALUES(`status`), '
                . '`is_display` = VALUES(`is_display`), '
                . '`updated_at` = VALUES(`updated_at`)',
            )
            ->execute($pdo);

        $this->insertRows(
            $pdo,
            'event_venues',
            ['event_id', 'venue_id', 'order_no'],
            array_map(
                fn (array $venue): array => [
                    $binEventId,
                    $this->converter->toBin($venue['venue_id']),
                    $venue['order_no'],
                ],
                $data['venues'],
            ),
        );

        $this->insertRows(
            $pdo,
            'event_media',
            ['event_id', 'media_id', 'order_no'],
            array_map(
                fn (array $media): array => [
                    $binEventId,
                    $this->converter->toBin($media['media_id']),
                    $media['order_no'],
                ],
                $data['media'],
            ),
        );

        $this->insertRows(
            $pdo,
            'event_sources',
            ['event_id', 'order_no', 'name', 'url'],
            array_map(
                static fn (array $source): array => [
                    $binEventId,
                    $source['order_no'],
                    $source['name'],
                    $source['url'],
                ],
                $data['sources'],
            ),
        );

        $this->insertRows(
            $pdo,
            'song_performances',
            ['performance_id', 'event_id', 'song_id', 'order_no', 'created_at', 'updated_at'],
            array_map(
                fn (array $performance): array => [
                    $this->converter->toBin($performance['performance_id']),
                    $binEventId,
                    $this->converter->toBin($performance['song_id']),
                    $performance['order_no'],
                    $now,
                    $now,
                ],
                $data['performances'],
            ),
        );

        $performancePersonRows = [];
        foreach ($data['performances'] as $performance) {
            foreach ($performance['co_vocalists'] as $person) {
                $performancePersonRows[] = [
                    $this->converter->toBin($performance['performance_id']),
                    $this->converter->toBin($person['person_id']),
                    $person['order_no'],
                    $person['credit_name'],
                ];
            }
        }
        $this->insertRows(
            $pdo,
            'song_performance_persons',
            ['performance_id', 'person_id', 'order_no', 'credit_name'],
            $performancePersonRows,
        );

        $this->insertRows(
            $pdo,
            'event_setlist_items',
            ['setlist_item_id', 'event_id', 'order_no', 'label'],
            array_map(
                fn (array $item): array => [
                    $this->converter->toBin($item['setlist_item_id']),
                    $binEventId,
                    $item['order_no'],
                    $item['label'],
                ],
                $data['setlist'],
            ),
        );

        $setlistPerformanceRows = [];
        foreach ($data['setlist'] as $item) {
            foreach ($item['performance_ids'] as $index => $performanceId) {
                $setlistPerformanceRows[] = [
                    $this->converter->toBin($item['setlist_item_id']),
                    $this->converter->toBin($performanceId),
                    $index + 1,
                ];
            }
        }
        $this->insertRows(
            $pdo,
            'event_setlist_item_performances',
            ['setlist_item_id', 'performance_id', 'order_no'],
            $setlistPerformanceRows,
        );

        return $event;
    }

    #[Override]
    public function delete(EventId $eventId): void
    {
        $this->queryFactory->delete()
            ->from(self::TABLE)
            ->where('event_id', '=', $this->converter->toBin($eventId->value))
            ->execute($this->queryFactory->pdo());
    }

    private function deleteChildren(PDOInterface $pdo, string $binEventId): void
    {
        // 共演者とセットリスト項目の披露参照は、親の削除で CASCADE される
        foreach (['event_setlist_items', 'song_performances', 'event_sources', 'event_media', 'event_venues'] as $table) {
            $this->queryFactory->delete()
                ->from($table)
                ->where('event_id', '=', $binEventId)
                ->execute($pdo);
        }
    }

    /**
     * @param list<string>      $columns
     * @param list<list<mixed>> $rows
     */
    private function insertRows(PDOInterface $pdo, string $table, array $columns, array $rows): void
    {
        if ($rows === []) {
            return;
        }

        $this->queryFactory->insert()
            ->into($table, $columns)
            ->values(...$rows)
            ->execute($pdo);
    }

    /** @param array<string, mixed> $row */
    private function hydrate(array $row): Event
    {
        $binEventId = Row::string($row, 'event_id');

        return Event::reconstruct(
            $this->converter->toUuid($binEventId),
            Row::string($row, 'title'),
            Row::string($row, 'description'),
            Row::int($row, 'type'),
            Row::nullableString($row, 'start_on'),
            Row::nullableString($row, 'end_on'),
            Row::int($row, 'status'),
            Row::bool($row, 'is_display'),
            $this->loadVenues($binEventId),
            $this->loadMedia($binEventId),
            $this->loadSources($binEventId),
            $this->loadPerformances($binEventId),
            $this->loadSetlist($binEventId),
        );
    }

    /** @return list<array{venueId: string, orderNo: int}> */
    private function loadVenues(string $binEventId): array
    {
        $rows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(['venue_id', 'order_no'])
                ->from('event_venues')
                ->where('event_id', '=', $binEventId)
                ->orderBy('order_no'),
        );

        return array_map(
            fn (array $row): array => [
                'venueId' => $this->converter->toUuid(Row::string($row, 'venue_id')),
                'orderNo' => Row::int($row, 'order_no'),
            ],
            $rows,
        );
    }

    /** @return list<array{mediaId: string, orderNo: int}> */
    private function loadMedia(string $binEventId): array
    {
        $rows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(['media_id', 'order_no'])
                ->from('event_media')
                ->where('event_id', '=', $binEventId)
                ->orderBy('order_no'),
        );

        return array_map(
            fn (array $row): array => [
                'mediaId' => $this->converter->toUuid(Row::string($row, 'media_id')),
                'orderNo' => Row::int($row, 'order_no'),
            ],
            $rows,
        );
    }

    /** @return list<_eventSourceInput> */
    private function loadSources(string $binEventId): array
    {
        $rows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(['name', 'url', 'order_no'])
                ->from('event_sources')
                ->where('event_id', '=', $binEventId)
                ->orderBy('order_no'),
        );

        return array_map(
            static fn (array $row): array => [
                'displayName' => Row::string($row, 'name'),
                'url' => Row::string($row, 'url'),
                'orderNo' => Row::int($row, 'order_no'),
            ],
            $rows,
        );
    }

    /** @return list<_songPerformanceInput> */
    private function loadPerformances(string $binEventId): array
    {
        $performanceRows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(['performance_id', 'song_id', 'order_no'])
                ->from('song_performances')
                ->where('event_id', '=', $binEventId)
                ->orderBy('order_no'),
        );

        $personRows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect([
                    'song_performance_persons.performance_id',
                    'song_performance_persons.person_id',
                    'song_performance_persons.order_no',
                    'song_performance_persons.credit_name',
                ])
                ->from('song_performance_persons')
                ->join('song_performances', 'song_performances.performance_id = song_performance_persons.performance_id')
                ->where('song_performances.event_id', '=', $binEventId)
                ->orderBy('song_performance_persons.order_no'),
        );

        $coVocalistsByPerformance = [];
        foreach ($personRows as $row) {
            $coVocalistsByPerformance[Row::string($row, 'performance_id')][] = [
                'personId' => $this->converter->toUuid(Row::string($row, 'person_id')),
                'creditName' => Row::nullableString($row, 'credit_name'),
                'orderNo' => Row::int($row, 'order_no'),
            ];
        }

        return array_map(
            fn (array $row): array => [
                'performanceId' => $this->converter->toUuid(Row::string($row, 'performance_id')),
                'songId' => $this->converter->toUuid(Row::string($row, 'song_id')),
                'orderNo' => Row::int($row, 'order_no'),
                'coVocalists' => $coVocalistsByPerformance[Row::string($row, 'performance_id')] ?? [],
            ],
            $performanceRows,
        );
    }

    /** @return list<_setlistItemInput> */
    private function loadSetlist(string $binEventId): array
    {
        $itemRows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(['setlist_item_id', 'order_no', 'label'])
                ->from('event_setlist_items')
                ->where('event_id', '=', $binEventId)
                ->orderBy('order_no'),
        );

        $linkRows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect([
                    'event_setlist_item_performances.setlist_item_id',
                    'event_setlist_item_performances.performance_id',
                ])
                ->from('event_setlist_item_performances')
                ->join(
                    'event_setlist_items',
                    'event_setlist_items.setlist_item_id = event_setlist_item_performances.setlist_item_id',
                )
                ->where('event_setlist_items.event_id', '=', $binEventId)
                ->orderBy('event_setlist_item_performances.order_no'),
        );

        $performanceIdsByItem = [];
        foreach ($linkRows as $row) {
            $performanceIdsByItem[Row::string($row, 'setlist_item_id')][] = $this->converter->toUuid(Row::string($row, 'performance_id'));
        }

        return array_map(
            fn (array $row): array => [
                'setlistItemId' => $this->converter->toUuid(Row::string($row, 'setlist_item_id')),
                'orderNo' => Row::int($row, 'order_no'),
                'label' => Row::nullableString($row, 'label'),
                'performanceIds' => $performanceIdsByItem[Row::string($row, 'setlist_item_id')] ?? [],
            ],
            $itemRows,
        );
    }
}
