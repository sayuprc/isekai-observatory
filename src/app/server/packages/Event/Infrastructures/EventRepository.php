<?php

declare(strict_types=1);

namespace Event\Infrastructures;

use Emonkak\Orm\SelectBuilder;
use Event\Domain\Criteria\EventSearchCriteria;
use Event\Domain\Models\Event;
use Event\Domain\Models\EventRepositoryInterface;
use Event\Domain\Models\EventStatus;
use Event\Domain\Models\EventType;
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
        $query = $this->applyCriteria($this->queryFactory->select()->from(self::TABLE), $criteria);
        $sort = $criteria->sort === 'title' ? 'title_lower' : 'start_on';
        $rows = $this->queryFactory->fetchAll(
            $query
                ->withSelect(self::COLUMNS)
                ->orderBy($sort, $criteria->order->value)
                ->orderBy('event_id')
                ->limit($criteria->perPage->value)
                ->offset($offset),
        );

        return array_map(fn (array $row): Event => $this->hydrate($row, false), $rows);
    }

    #[Override]
    public function maxPage(EventSearchCriteria $criteria): int
    {
        $count = Row::intValue($this->applyCriteria($this->queryFactory->select()->from(self::TABLE), $criteria)->aggregate($this->queryFactory->pdo(), 'COUNT(*)'));

        return (int)ceil($count / $criteria->perPage->value);
    }

    #[Override]
    public function find(string $eventId): ?Event
    {
        $rows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(self::COLUMNS)
                ->from(self::TABLE)
                ->where('event_id', '=', $this->converter->toBin($eventId))
                ->limit(1),
        );

        return isset($rows[0]) ? $this->hydrate($rows[0], true) : null;
    }

    #[Override]
    public function save(Event $event): Event
    {
        $pdo = $this->queryFactory->pdo();
        $binEventId = $this->converter->toBin($event->eventId);
        $now = now()->toDateTimeString();

        $setlistItems = $this->queryFactory->fetchAll($this->queryFactory->select()->from('event_setlist_items')->withSelect(['setlist_item_id'])->where('event_id', '=', $binEventId));
        $performanceRows = $this->queryFactory->fetchAll($this->queryFactory->select()->from('song_performances')->withSelect(['performance_id'])->where('event_id', '=', $binEventId));
        $setlistIds = array_map(static fn (array $row): string => Row::string($row, 'setlist_item_id'), $setlistItems);
        $performanceIds = array_map(static fn (array $row): string => Row::string($row, 'performance_id'), $performanceRows);
        if ($setlistIds !== []) {
            $this->queryFactory->delete()->from('event_setlist_item_performances')->where('setlist_item_id', 'IN', $setlistIds)->execute($pdo);
        }
        if ($performanceIds !== []) {
            $this->queryFactory->delete()->from('song_performance_persons')->where('performance_id', 'IN', $performanceIds)->execute($pdo);
        }
        $this->queryFactory->delete()->from('event_setlist_items')->where('event_id', '=', $binEventId)->execute($pdo);
        $this->queryFactory->delete()->from('song_performances')->where('event_id', '=', $binEventId)->execute($pdo);
        foreach (['event_sources', 'event_media', 'event_venues'] as $table) {
            $this->queryFactory->delete()->from($table)->where('event_id', '=', $binEventId)->execute($pdo);
        }

        $data = $event->toArray();
        $this->queryFactory->insert()
            ->into(self::TABLE, ['event_id', 'title', 'description', 'type', 'start_on', 'end_on', 'status', 'is_display', 'created_at', 'updated_at'])
            ->values([
                $binEventId, $data['title'], $data['description'], $data['type'], $data['start_on'], $data['end_on'],
                $data['status'], $data['is_display'], $now, $now,
            ])
            ->build()
            ->append('ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `description` = VALUES(`description`), `type` = VALUES(`type`), `start_on` = VALUES(`start_on`), `end_on` = VALUES(`end_on`), `status` = VALUES(`status`), `is_display` = VALUES(`is_display`), `updated_at` = VALUES(`updated_at`)')
            ->execute($pdo);

        $venueRows = [];
        foreach ($event->venues as $index => $venue) {
            $venueRows[] = [$binEventId, $this->converter->toBin($venue['venue_id']), $venue['order_no'] ?: $index + 1];
        }
        if ($venueRows !== []) {
            $this->queryFactory->insert()->into('event_venues', ['event_id', 'venue_id', 'order_no'])->values(...$venueRows)->execute($pdo);
        }

        $mediaRows = [];
        foreach ($event->media as $index => $media) {
            $mediaRows[] = [$binEventId, $this->converter->toBin($media['media_id']), $media['order_no'] ?: $index + 1];
        }
        if ($mediaRows !== []) {
            $this->queryFactory->insert()->into('event_media', ['event_id', 'media_id', 'order_no'])->values(...$mediaRows)->execute($pdo);
        }

        $sourceRows = array_map(static fn (array $source): array => [$binEventId, $source['order_no'], $source['name'], $source['url']], $event->sources);
        if ($sourceRows !== []) {
            $this->queryFactory->insert()->into('event_sources', ['event_id', 'order_no', 'name', 'url'])->values(...$sourceRows)->execute($pdo);
        }

        $performances = $this->allPerformances($event);
        $performanceRows = [];
        foreach ($performances as $performance) {
            $performanceRows[] = [
                $this->converter->toBin($performance['performance_id']), $binEventId,
                $this->converter->toBin($performance['song_id']), $performance['order_no'], $now, $now,
            ];
        }
        if ($performanceRows !== []) {
            $this->queryFactory->insert()->into('song_performances', ['performance_id', 'event_id', 'song_id', 'order_no', 'created_at', 'updated_at'])->values(...$performanceRows)->execute($pdo);
        }

        $personRows = [];
        foreach ($performances as $performance) {
            foreach ($performance['co_vocalists'] as $person) {
                $personRows[] = [$this->converter->toBin($performance['performance_id']), $this->converter->toBin($person['person_id']), $person['order_no'], $person['credit_name']];
            }
        }
        if ($personRows !== []) {
            $this->queryFactory->insert()->into('song_performance_persons', ['performance_id', 'person_id', 'order_no', 'credit_name'])->values(...$personRows)->execute($pdo);
        }

        $setlistRows = [];
        $setlistPerformanceRows = [];
        foreach ($event->setlist as $item) {
            $setlistRows[] = [$this->converter->toBin($item['setlist_item_id']), $binEventId, $item['order_no'], $item['label']];
            foreach ($item['performances'] as $index => $performance) {
                $setlistPerformanceRows[] = [$this->converter->toBin($item['setlist_item_id']), $this->converter->toBin($performance['performance_id']), $index + 1];
            }
        }
        if ($setlistRows !== []) {
            $this->queryFactory->insert()->into('event_setlist_items', ['setlist_item_id', 'event_id', 'order_no', 'label'])->values(...$setlistRows)->execute($pdo);
        }
        if ($setlistPerformanceRows !== []) {
            $this->queryFactory->insert()->into('event_setlist_item_performances', ['setlist_item_id', 'performance_id', 'order_no'])->values(...$setlistPerformanceRows)->execute($pdo);
        }

        return $event;
    }

    #[Override]
    public function delete(string $eventId): void
    {
        $this->queryFactory->delete()->from(self::TABLE)->where('event_id', '=', $this->converter->toBin($eventId))->execute($this->queryFactory->pdo());
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
    private function hydrate(array $row, bool $withChildren): Event
    {
        $binEventId = Row::string($row, 'event_id');
        $event = new Event(
            $this->converter->toUuid($binEventId),
            Row::string($row, 'title'),
            Row::string($row, 'description'),
            EventType::from(Row::int($row, 'type')),
            Row::nullableString($row, 'start_on'),
            Row::nullableString($row, 'end_on'),
            EventStatus::from(Row::int($row, 'status')),
            Row::bool($row, 'is_display'),
        );

        if (! $withChildren) {
            return $event;
        }

        $event->venues = $this->loadVenues($binEventId);
        $event->media = $this->loadMedia($binEventId);
        $event->sources = $this->loadSources($binEventId);
        $event->performances = $this->loadPerformances($binEventId);
        $event->setlist = $this->loadSetlist($binEventId, $event->performances);

        return $event;
    }

    /** @return list<array{venue_id: string, name: string, kind: int, order_no: int}> */
    private function loadVenues(string $binEventId): array
    {
        $links = $this->queryFactory->fetchAll($this->queryFactory->select()->from('event_venues')->withSelect(['venue_id', 'order_no'])->where('event_id', '=', $binEventId)->orderBy('order_no'));
        $result = [];
        foreach ($links as $link) {
            $id = Row::string($link, 'venue_id');
            $rows = $this->queryFactory->fetchAll($this->queryFactory->select()->from('venues')->withSelect(['venue_id', 'name', 'kind'])->where('venue_id', '=', $id)->limit(1));
            if ($rows === []) {
                continue;
            }
            $row = $rows[0];
            $result[] = ['venue_id' => $this->converter->toUuid($id), 'name' => Row::string($row, 'name'), 'kind' => Row::int($row, 'kind'), 'order_no' => Row::int($link, 'order_no')];
        }

        return $result;
    }

    /** @return list<array{media_id: string, title: string, url: string, published_at: string, type: int, is_display: bool, order_no: int}> */
    private function loadMedia(string $binEventId): array
    {
        $links = $this->queryFactory->fetchAll($this->queryFactory->select()->from('event_media')->withSelect(['media_id', 'order_no'])->where('event_id', '=', $binEventId)->orderBy('order_no'));
        $result = [];
        foreach ($links as $link) {
            $id = Row::string($link, 'media_id');
            $rows = $this->queryFactory->fetchAll($this->queryFactory->select()->from('media')->withSelect(['media_id', 'title', 'url', 'published_at', 'type', 'is_display'])->where('media_id', '=', $id)->limit(1));
            if ($rows === []) {
                continue;
            }
            $row = $rows[0];
            $result[] = ['media_id' => $this->converter->toUuid($id), 'title' => Row::string($row, 'title'), 'url' => Row::string($row, 'url'), 'published_at' => Row::string($row, 'published_at'), 'type' => Row::int($row, 'type'), 'is_display' => Row::bool($row, 'is_display'), 'order_no' => Row::int($link, 'order_no')];
        }

        return $result;
    }

    /** @return list<array{name: string, url: string, order_no: int}> */
    private function loadSources(string $binEventId): array
    {
        return array_map(static fn (array $row): array => ['name' => Row::string($row, 'name'), 'url' => Row::string($row, 'url'), 'order_no' => Row::int($row, 'order_no')], $this->queryFactory->fetchAll($this->queryFactory->select()->from('event_sources')->withSelect(['name', 'url', 'order_no'])->where('event_id', '=', $binEventId)->orderBy('order_no')));
    }

    /** @return list<array{performance_id: string, song_id: string, song_title: string, order_no: int, song_is_display: bool, co_vocalists: list<array{person_id: string, name: string, credit_name: ?string, order_no: int}>}> */
    private function loadPerformances(string $binEventId): array
    {
        $rows = $this->queryFactory->fetchAll($this->queryFactory->select()->from('song_performances')->withSelect(['performance_id', 'song_id', 'order_no'])->where('event_id', '=', $binEventId)->orderBy('order_no'));
        $result = [];
        foreach ($rows as $row) {
            $songId = Row::string($row, 'song_id');
            $songRows = $this->queryFactory->fetchAll($this->queryFactory->select()->from('songs')->withSelect(['title', 'is_display'])->where('song_id', '=', $songId)->limit(1));
            $people = $this->queryFactory->fetchAll($this->queryFactory->select()->from('song_performance_persons')->withSelect(['person_id', 'order_no', 'credit_name'])->where('performance_id', '=', Row::string($row, 'performance_id'))->orderBy('order_no'));
            $coVocalists = [];
            foreach ($people as $person) {
                $personId = Row::string($person, 'person_id');
                $personRows = $this->queryFactory->fetchAll($this->queryFactory->select()->from('persons')->withSelect(['name'])->where('person_id', '=', $personId)->limit(1));
                if ($personRows === []) {
                    continue;
                }
                $coVocalists[] = ['person_id' => $this->converter->toUuid($personId), 'name' => Row::string($personRows[0], 'name'), 'credit_name' => Row::nullableString($person, 'credit_name'), 'order_no' => Row::int($person, 'order_no')];
            }
            $result[] = ['performance_id' => $this->converter->toUuid(Row::string($row, 'performance_id')), 'song_id' => $this->converter->toUuid($songId), 'song_title' => $songRows === [] ? '' : Row::string($songRows[0], 'title'), 'order_no' => Row::int($row, 'order_no'), 'song_is_display' => $songRows !== [] && Row::bool($songRows[0], 'is_display'), 'co_vocalists' => $coVocalists];
        }

        return $result;
    }

    /**
     * @param list<array{performance_id: string, song_id: string, song_title: string, order_no: int, song_is_display: bool, co_vocalists: list<array{person_id: string, name: string, credit_name: ?string, order_no: int}>}> $performances
     *
     * @return list<array{setlist_item_id: string, order_no: int, label: ?string, performances: list<array{performance_id: string, song_id: string, song_title: string, order_no: int, song_is_display: bool, co_vocalists: list<array{person_id: string, name: string, credit_name: ?string, order_no: int}>}>}>
     */
    private function loadSetlist(string $binEventId, array $performances): array
    {
        /** @var array<string, array{performance_id: string, song_id: string, song_title: string, order_no: int, song_is_display: bool, co_vocalists: list<array{person_id: string, name: string, credit_name: ?string, order_no: int}>}> $byId */
        $byId = array_column($performances, null, 'performance_id');
        $items = $this->queryFactory->fetchAll($this->queryFactory->select()->from('event_setlist_items')->withSelect(['setlist_item_id', 'order_no', 'label'])->where('event_id', '=', $binEventId)->orderBy('order_no'));
        $result = [];
        foreach ($items as $item) {
            $itemId = Row::string($item, 'setlist_item_id');
            $links = $this->queryFactory->fetchAll($this->queryFactory->select()->from('event_setlist_item_performances')->withSelect(['performance_id'])->where('setlist_item_id', '=', $itemId)->orderBy('order_no'));
            /** @var list<array{performance_id: string, song_id: string, song_title: string, order_no: int, song_is_display: bool, co_vocalists: list<array{person_id: string, name: string, credit_name: ?string, order_no: int}>}> $itemPerformances */
            $itemPerformances = [];
            foreach ($links as $link) {
                $id = $this->converter->toUuid(Row::string($link, 'performance_id'));
                if (isset($byId[$id])) {
                    $itemPerformances[] = $byId[$id];
                }
            }
            $result[] = ['setlist_item_id' => $this->converter->toUuid($itemId), 'order_no' => Row::int($item, 'order_no'), 'label' => Row::nullableString($item, 'label'), 'performances' => $itemPerformances];
        }

        return $result;
    }

    /** @return list<array{performance_id: string, song_id: string, song_title: string, order_no: int, song_is_display: bool, co_vocalists: list<array{person_id: string, name: string, credit_name: ?string, order_no: int}>}> */
    private function allPerformances(Event $event): array
    {
        $all = [];
        foreach ($event->performances as $performance) {
            $all[$performance['performance_id']] = $performance;
        }
        foreach (array_merge([], ...array_map(static fn (array $item): array => $item['performances'], $event->setlist)) as $performance) {
            $all[$performance['performance_id']] ??= $performance;
        }

        return array_values($all);
    }
}
