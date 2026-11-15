<?php

declare(strict_types=1);

namespace Event\Infrastructures;

use Event\Domain\Models\Event;
use Event\Domain\Models\EventId;
use Event\Domain\Models\EventRepositoryInterface;
use Override;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Infrastructures\Database\QueryFactory;
use Support\Infrastructures\Database\Row;

readonly class EventRepository implements EventRepositoryInterface
{
    private const string TABLE = 'events';

    /** @var array<int, string> */
    private const array COLUMNS = [
        'event_id',
        'title',
        'description',
        'note',
        'type',
        'start_on',
        'end_on',
        'status',
        'is_display',
    ];

    public function __construct(
        private QueryFactory $queryFactory,
        private UuidConverterInterface $converter,
        private EventChildrenLoader $children,
    ) {
    }

    #[Override]
    public function find(EventId $eventId): ?Event
    {
        return $this->findByIds($eventId)[0] ?? null;
    }

    #[Override]
    public function findByIds(EventId ...$eventIds): array
    {
        if ($eventIds === []) {
            return [];
        }

        $binEventIds = array_map(
            fn (EventId $eventId): string => $this->converter->toBin($eventId->value),
            array_values($eventIds),
        );

        $rows = $this->queryFactory
            ->select()
            ->withSelect(self::COLUMNS)
            ->from(self::TABLE)
            ->where('event_id', 'IN', $binEventIds)
            |> $this->queryFactory->fetchAll(...);

        if ($rows === []) {
            return [];
        }

        // 子テーブルはイベント ID でまとめて引き、イベントごとの問い合わせを避ける
        $venues = $this->children->loadVenues($binEventIds);
        $media = $this->children->loadMedia($binEventIds);
        $releases = $this->children->loadReleases($binEventIds);
        $sources = $this->children->loadSources($binEventIds);
        $performances = $this->children->loadPerformances($binEventIds);
        $setlist = $this->children->loadSetlist($binEventIds);
        $tags = $this->children->loadTags($binEventIds);

        return array_map(
            function (array $row) use ($venues, $media, $releases, $sources, $performances, $setlist, $tags): Event {
                $binEventId = Row::string($row, 'event_id');

                return Event::reconstruct(
                    $this->converter->toUuid($binEventId),
                    Row::string($row, 'title'),
                    Row::string($row, 'description'),
                    Row::string($row, 'note'),
                    Row::int($row, 'type'),
                    Row::nullableString($row, 'start_on'),
                    Row::nullableString($row, 'end_on'),
                    Row::int($row, 'status'),
                    Row::bool($row, 'is_display'),
                    $venues[$binEventId] ?? [],
                    $media[$binEventId] ?? [],
                    $releases[$binEventId] ?? [],
                    $sources[$binEventId] ?? [],
                    $performances[$binEventId] ?? [],
                    $setlist[$binEventId] ?? [],
                    $tags[$binEventId] ?? [],
                );
            },
            $rows,
        );
    }

    #[Override]
    public function save(Event $event): Event
    {
        $binEventId = $this->converter->toBin($event->eventId->value);
        $now = now()->toDateTimeString();
        $data = $event->toArray();

        // 共演者とセットリスト項目の披露参照は親の削除で CASCADE されるため、削除対象に含めない
        $this->queryFactory->deleteFromTables(
            [
                'event_setlist_items',
                'song_performances',
                'event_sources',
                'event_releases',
                'event_taggings',
                'event_media',
                'event_venues',
            ],
            'event_id',
            $binEventId,
        );

        $this->queryFactory
            ->insert()
            ->into(self::TABLE, [...self::COLUMNS, 'created_at', 'updated_at'])
            ->values([
                $binEventId,
                $data['title'],
                $data['description'],
                $data['note'],
                $data['type'],
                $data['start_on'],
                $data['end_on'],
                $data['status'],
                $data['is_display'],
                $now,
                $now,
            ])
            ->build()
            ->append(<<<SQL
                ON DUPLICATE KEY UPDATE
                `title` = VALUES(`title`),
                `description` = VALUES(`description`),
                `note` = VALUES(`note`),
                `type` = VALUES(`type`),
                `start_on` = VALUES(`start_on`),
                `end_on` = VALUES(`end_on`),
                `status` = VALUES(`status`),
                `is_display` = VALUES(`is_display`),
                `updated_at` = VALUES(`updated_at`)
                SQL)
            ->execute($this->queryFactory->pdo());

        $this->queryFactory->insertRows(
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

        $this->queryFactory->insertRows(
            'event_taggings',
            ['event_id', 'tag_id'],
            array_map(
                fn (array $tag): array => [$binEventId, $this->converter->toBin($tag['tag_id'])],
                $data['tags'],
            ),
        );

        $this->queryFactory->insertRows(
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

        $this->queryFactory->insertRows(
            'event_releases',
            ['event_id', 'release_id', 'order_no'],
            array_map(
                fn (array $release): array => [
                    $binEventId,
                    $this->converter->toBin($release['release_id']),
                    $release['order_no'],
                ],
                $data['releases'],
            ),
        );

        $this->queryFactory->insertRows(
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

        $this->queryFactory->insertRows(
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
                    $person['person_group_id'] === null ? null : $this->converter->toBin($person['person_group_id']),
                ];
            }
        }
        $this->queryFactory->insertRows(
            'song_performance_persons',
            ['performance_id', 'person_id', 'order_no', 'credit_name', 'person_group_id'],
            $performancePersonRows,
        );

        $this->queryFactory->insertRows(
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
        $this->queryFactory->insertRows(
            'event_setlist_item_performances',
            ['setlist_item_id', 'performance_id', 'order_no'],
            $setlistPerformanceRows,
        );

        return $event;
    }

    #[Override]
    public function delete(EventId $eventId): void
    {
        $this->queryFactory
            ->delete()
            ->from(self::TABLE)
            ->where('event_id', '=', $this->converter->toBin($eventId->value))
            ->execute($this->queryFactory->pdo());
    }
}
