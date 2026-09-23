<?php

declare(strict_types=1);

namespace Event\Infrastructures;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\SelectBuilder;
use Event\Domain\Criteria\EventSearchCriteria;
use Event\Domain\Models\Event;
use Event\Domain\Models\EventId;
use Event\Domain\Models\EventRepositoryInterface;
use Override;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Infrastructures\Database\QueryFactory;
use Support\Infrastructures\Database\Row;
use Support\Infrastructures\Database\SqlHelper;

readonly class EventRepository implements EventRepositoryInterface
{
    private const string TABLE = 'events';

    /** @var list<string> */
    private const array COLUMNS = [
        'event_id', 'title', 'description', 'type', 'start_on', 'end_on', 'status', 'is_display',
    ];

    public function __construct(
        private QueryFactory $queryFactory,
        private UuidConverterInterface $converter,
    ) {
    }

    #[Override]
    public function search(EventSearchCriteria $criteria): array
    {
        $offset = ($criteria->page - 1) * $criteria->perPage->value;
        $sort = $criteria->sort === 'title' ? 'title_lower' : 'start_on';
        $rows = $this->queryFactory->fetchAll(
            $this->applyCriteria($this->queryFactory->select()->from(self::TABLE), $criteria)
                ->withSelect(self::COLUMNS)
                ->orderBy($sort, $criteria->order->value)
                ->orderBy('event_id')
                ->limit($criteria->perPage->value)
                ->offset($offset),
        );

        return array_map($this->hydrate(...), $rows);
    }

    #[Override]
    public function maxPage(EventSearchCriteria $criteria): int
    {
        $count = Row::intValue($this->applyCriteria($this->queryFactory->select()->from(self::TABLE), $criteria)->aggregate($this->queryFactory->pdo(), 'COUNT(*)'));

        return (int)ceil($count / $criteria->perPage->value);
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

        $this->deleteChildren($pdo, $binEventId);

        $this->queryFactory->insert()
            ->into(self::TABLE, [...self::COLUMNS, 'created_at', 'updated_at'])
            ->values([
                $binEventId, $data['title'], $data['description'], $data['type'], $data['start_on'], $data['end_on'],
                $data['status'], $data['is_display'], $now, $now,
            ])
            ->build()
            ->append('ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `description` = VALUES(`description`), `type` = VALUES(`type`), `start_on` = VALUES(`start_on`), `end_on` = VALUES(`end_on`), `status` = VALUES(`status`), `is_display` = VALUES(`is_display`), `updated_at` = VALUES(`updated_at`)')
            ->execute($pdo);

        $this->insertRows($pdo, 'event_venues', ['event_id', 'venue_id', 'order_no'], array_map(
            fn (array $venue): array => [$binEventId, $this->converter->toBin($venue['venue_id']), $venue['order_no']],
            $data['venues'],
        ));
        $this->insertRows($pdo, 'event_media', ['event_id', 'media_id', 'order_no'], array_map(
            fn (array $media): array => [$binEventId, $this->converter->toBin($media['media_id']), $media['order_no']],
            $data['media'],
        ));
        $this->insertRows($pdo, 'event_sources', ['event_id', 'order_no', 'name', 'url'], array_map(
            static fn (array $source): array => [$binEventId, $source['order_no'], $source['name'], $source['url']],
            $data['sources'],
        ));
        $this->insertRows($pdo, 'song_performances', ['performance_id', 'event_id', 'song_id', 'order_no', 'created_at', 'updated_at'], array_map(
            fn (array $performance): array => [
                $this->converter->toBin($performance['performance_id']), $binEventId,
                $this->converter->toBin($performance['song_id']), $performance['order_no'], $now, $now,
            ],
            $data['performances'],
        ));
        $this->insertRows($pdo, 'song_performance_persons', ['performance_id', 'person_id', 'order_no', 'credit_name'], array_merge([], ...array_map(
            fn (array $performance): array => array_map(
                fn (array $person): array => [
                    $this->converter->toBin($performance['performance_id']), $this->converter->toBin($person['person_id']),
                    $person['order_no'], $person['credit_name'],
                ],
                $performance['co_vocalists'],
            ),
            $data['performances'],
        )));
        $this->insertRows($pdo, 'event_setlist_items', ['setlist_item_id', 'event_id', 'order_no', 'label'], array_map(
            fn (array $item): array => [$this->converter->toBin($item['setlist_item_id']), $binEventId, $item['order_no'], $item['label']],
            $data['setlist'],
        ));
        $this->insertRows($pdo, 'event_setlist_item_performances', ['setlist_item_id', 'performance_id', 'order_no'], array_merge([], ...array_map(
            fn (array $item): array => array_map(
                fn (string $performanceId, int $index): array => [$this->converter->toBin($item['setlist_item_id']), $this->converter->toBin($performanceId), $index + 1],
                $item['performance_ids'],
                array_keys($item['performance_ids']),
            ),
            $data['setlist'],
        )));

        return $event;
    }

    #[Override]
    public function delete(EventId $eventId): void
    {
        $this->queryFactory->delete()->from(self::TABLE)->where('event_id', '=', $this->converter->toBin($eventId->value))->execute($this->queryFactory->pdo());
    }

    private function deleteChildren(PDOInterface $pdo, string $binEventId): void
    {
        $setlistIds = array_column($this->queryFactory->fetchAll($this->queryFactory->select()->from('event_setlist_items')->withSelect(['setlist_item_id'])->where('event_id', '=', $binEventId)), 'setlist_item_id');
        $performanceIds = array_column($this->queryFactory->fetchAll($this->queryFactory->select()->from('song_performances')->withSelect(['performance_id'])->where('event_id', '=', $binEventId)), 'performance_id');
        if ($setlistIds !== []) {
            $this->queryFactory->delete()->from('event_setlist_item_performances')->where('setlist_item_id', 'IN', $setlistIds)->execute($pdo);
        }
        if ($performanceIds !== []) {
            $this->queryFactory->delete()->from('song_performance_persons')->where('performance_id', 'IN', $performanceIds)->execute($pdo);
        }
        foreach (['event_setlist_items', 'song_performances', 'event_sources', 'event_media', 'event_venues'] as $table) {
            $this->queryFactory->delete()->from($table)->where('event_id', '=', $binEventId)->execute($pdo);
        }
    }

    /**
     * @param list<string>      $columns
     * @param list<list<mixed>> $rows
     */
    private function insertRows(PDOInterface $pdo, string $table, array $columns, array $rows): void
    {
        if ($rows !== []) {
            $this->queryFactory->insert()->into($table, $columns)->values(...$rows)->execute($pdo);
        }
    }

    private function applyCriteria(SelectBuilder $query, EventSearchCriteria $criteria): SelectBuilder
    {
        if ($criteria->title !== null && $criteria->title !== '') {
            $query = $query->where('title_lower', 'LIKE', '%' . SqlHelper::escapeLike(mb_strtolower($criteria->title)) . '%');
        }
        if ($criteria->type !== null) {
            $query = $query->where('type', '=', $criteria->type);
        }
        if ($criteria->status !== null) {
            $query = $query->where('status', '=', $criteria->status);
        }
        if ($criteria->isDisplay !== null) {
            $query = $query->where('is_display', '=', $criteria->isDisplay);
        }

        return $query;
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
        return array_map(
            fn (array $row): array => ['venueId' => $this->converter->toUuid(Row::string($row, 'venue_id')), 'orderNo' => Row::int($row, 'order_no')],
            $this->queryFactory->fetchAll($this->queryFactory->select()->from('event_venues')->withSelect(['venue_id', 'order_no'])->where('event_id', '=', $binEventId)->orderBy('order_no')),
        );
    }

    /** @return list<array{mediaId: string, orderNo: int}> */
    private function loadMedia(string $binEventId): array
    {
        return array_map(
            fn (array $row): array => ['mediaId' => $this->converter->toUuid(Row::string($row, 'media_id')), 'orderNo' => Row::int($row, 'order_no')],
            $this->queryFactory->fetchAll($this->queryFactory->select()->from('event_media')->withSelect(['media_id', 'order_no'])->where('event_id', '=', $binEventId)->orderBy('order_no')),
        );
    }

    /** @return list<array{displayName: string, url: string, orderNo: int}> */
    private function loadSources(string $binEventId): array
    {
        return array_map(
            static fn (array $row): array => ['displayName' => Row::string($row, 'name'), 'url' => Row::string($row, 'url'), 'orderNo' => Row::int($row, 'order_no')],
            $this->queryFactory->fetchAll($this->queryFactory->select()->from('event_sources')->withSelect(['name', 'url', 'order_no'])->where('event_id', '=', $binEventId)->orderBy('order_no')),
        );
    }

    /** @return list<array{performanceId: string, songId: string, orderNo: int, coVocalists: list<array{personId: string, creditName: ?string, orderNo: int}>}> */
    private function loadPerformances(string $binEventId): array
    {
        return array_map(
            fn (array $row): array => [
                'performanceId' => $this->converter->toUuid(Row::string($row, 'performance_id')),
                'songId' => $this->converter->toUuid(Row::string($row, 'song_id')),
                'orderNo' => Row::int($row, 'order_no'),
                'coVocalists' => array_map(
                    fn (array $person): array => [
                        'personId' => $this->converter->toUuid(Row::string($person, 'person_id')),
                        'creditName' => Row::nullableString($person, 'credit_name'),
                        'orderNo' => Row::int($person, 'order_no'),
                    ],
                    $this->queryFactory->fetchAll($this->queryFactory->select()->from('song_performance_persons')->withSelect(['person_id', 'order_no', 'credit_name'])->where('performance_id', '=', Row::string($row, 'performance_id'))->orderBy('order_no')),
                ),
            ],
            $this->queryFactory->fetchAll($this->queryFactory->select()->from('song_performances')->withSelect(['performance_id', 'song_id', 'order_no'])->where('event_id', '=', $binEventId)->orderBy('order_no')),
        );
    }

    /** @return list<array{setlistItemId: string, orderNo: int, label: ?string, performanceIds: list<string>}> */
    private function loadSetlist(string $binEventId): array
    {
        return array_map(
            fn (array $row): array => [
                'setlistItemId' => $this->converter->toUuid(Row::string($row, 'setlist_item_id')),
                'orderNo' => Row::int($row, 'order_no'),
                'label' => Row::nullableString($row, 'label'),
                'performanceIds' => array_map(
                    fn (array $link): string => $this->converter->toUuid(Row::string($link, 'performance_id')),
                    $this->queryFactory->fetchAll($this->queryFactory->select()->from('event_setlist_item_performances')->withSelect(['performance_id'])->where('setlist_item_id', '=', Row::string($row, 'setlist_item_id'))->orderBy('order_no')),
                ),
            ],
            $this->queryFactory->fetchAll($this->queryFactory->select()->from('event_setlist_items')->withSelect(['setlist_item_id', 'order_no', 'label'])->where('event_id', '=', $binEventId)->orderBy('order_no')),
        );
    }
}
