<?php

declare(strict_types=1);

namespace Event\Domain\Models;

class Event
{
    /**
     * @param list<array{venue_id: string, name: string, kind: int, order_no: int}>                                                                                                                                                                                                                                                $venues
     * @param list<array{media_id: string, title: string, url: string, published_at: string, type: int, is_display: bool, order_no: int}>                                                                                                                                                                                          $media
     * @param list<array{name: string, url: string, order_no: int}>                                                                                                                                                                                                                                                                $sources
     * @param list<array{performance_id: string, song_id: string, song_title: string, order_no: int, is_display: bool, song_is_display: bool, co_vocalists: list<array{person_id: string, name: string, credit_name: ?string, order_no: int}>}>                                                                                    $performances
     * @param list<array{setlist_item_id: string, order_no: int, label: ?string, performances: list<array{performance_id: string, song_id: string, song_title: string, order_no: int, is_display: bool, song_is_display: bool, co_vocalists: list<array{person_id: string, name: string, credit_name: ?string, order_no: int}>}>}> $setlist
     */
    public function __construct(
        public string $eventId,
        public string $title,
        public ?string $description,
        public EventType $type,
        public EventScheduleType $scheduleType,
        public ?string $startDate,
        public ?string $endDate,
        public ?string $startAt,
        public ?string $endAt,
        public ?string $timeZone,
        public ?EventStatus $status,
        public ?string $postponedToEventId,
        public bool $isDisplay,
        public array $venues = [],
        public array $media = [],
        public array $sources = [],
        public array $performances = [],
        public array $setlist = [],
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromInput(string $eventId, array $data): self
    {
        $schedule = self::asArray(self::at($data, 'schedule'));

        return new self(
            $eventId,
            self::stringValue(self::at($data, 'title')),
            self::nullableString(self::at($data, 'description')),
            EventType::from(self::intValue(self::at($data, 'typeValue'))),
            EventScheduleType::from(self::intValue(self::at($schedule, 'type'), EventScheduleType::Undated->value)),
            self::nullableString(self::at($schedule, 'startDate')),
            self::nullableString(self::at($schedule, 'endDate')),
            self::nullableString(self::at($schedule, 'startDateTime')),
            self::nullableString(self::at($schedule, 'endDateTime')),
            self::nullableString(self::at($schedule, 'timeZone')),
            self::nullableEnumValue(self::at($data, 'statusValue'), EventStatus::class),
            self::nullableString(self::at($data, 'postponedToEventId')),
            self::boolValue(self::at($data, 'isDisplay')),
            self::venueIds(self::at($data, 'venueIds')),
            self::mediaIds(self::at($data, 'mediaIds')),
            self::sources(self::at($data, 'sources')),
            self::performances(self::at($data, 'performances')),
            self::setlist(self::at($data, 'setlist')),
        );
    }

    /** @return array{event_id: string, title: string, description: ?string, type: int, schedule_type: int, start_date: ?string, end_date: ?string, start_at: ?string, end_at: ?string, time_zone: ?string, status: ?int, postponed_to_event_id: ?string, is_display: bool} */
    public function toArray(): array
    {
        return [
            'event_id' => $this->eventId,
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type->value,
            'schedule_type' => $this->scheduleType->value,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'start_at' => $this->startAt,
            'end_at' => $this->endAt,
            'time_zone' => $this->timeZone,
            'status' => $this->status?->value,
            'postponed_to_event_id' => $this->postponedToEventId,
            'is_display' => $this->isDisplay,
        ];
    }

    /** @return array{type: int, startDate: ?string, endDate: ?string, startDateTime: ?string, endDateTime: ?string, timeZone: ?string} */
    public function schedule(): array
    {
        return [
            'type' => $this->scheduleType->value,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'startDateTime' => $this->startAt,
            'endDateTime' => $this->endAt,
            'timeZone' => $this->timeZone,
        ];
    }

    /** @param array<mixed, mixed> $data */
    private static function at(array $data, string $key): mixed
    {
        return $data[$key] ?? null;
    }

    /** @return array<mixed, mixed> */
    private static function asArray(mixed $value): array
    {
        return is_array($value) ? $value : [];
    }

    private static function stringValue(mixed $value): string
    {
        return is_scalar($value) ? (string)$value : '';
    }

    private static function intValue(mixed $value, int $default = 0): int
    {
        return is_int($value) || is_float($value) || is_string($value) && is_numeric($value) ? (int)$value : $default;
    }

    private static function boolValue(mixed $value): bool
    {
        return is_bool($value) ? $value : (bool)$value;
    }

    /** @param class-string<EventStatus> $enum */
    private static function nullableEnumValue(mixed $value, string $enum): ?EventStatus
    {
        return $value === null || $value === '' ? null : $enum::from(self::intValue($value));
    }

    private static function nullableString(mixed $value): ?string
    {
        return $value === null || $value === '' ? null : self::stringValue($value);
    }

    /**
     * @return list<array{name: string, url: string, order_no: int}>
     */
    private static function sources(mixed $value): array
    {
        $result = [];
        foreach (self::asArray($value) as $source) {
            $sourceData = self::asArray($source);
            $result[] = [
                'name' => self::stringValue(self::at($sourceData, 'displayName') ?? self::at($sourceData, 'name')),
                'url' => self::stringValue(self::at($sourceData, 'url')),
                'order_no' => self::intValue(self::at($sourceData, 'orderNo'), count($result) + 1),
            ];
        }

        return $result;
    }

    /**
     * @return list<array{performance_id: string, song_id: string, song_title: string, order_no: int, is_display: bool, song_is_display: bool, co_vocalists: list<array{person_id: string, name: string, credit_name: ?string, order_no: int}>}>
     */
    private static function performances(mixed $value): array
    {
        $result = [];
        foreach (self::asArray($value) as $performance) {
            $performanceData = self::asArray($performance);
            $result[] = [
                'performance_id' => self::stringValue(self::at($performanceData, 'performanceId')) ?: self::uuid(),
                'song_id' => self::stringValue(self::at($performanceData, 'songId')),
                'song_title' => self::stringValue(self::at($performanceData, 'songTitle')),
                'order_no' => self::intValue(self::at($performanceData, 'orderNo'), count($result) + 1),
                'is_display' => self::boolValue(self::at($performanceData, 'isDisplay')),
                'song_is_display' => true,
                'co_vocalists' => self::coVocalists(self::at($performanceData, 'coVocalists')),
            ];
        }

        return $result;
    }

    /**
     * @return list<array{setlist_item_id: string, order_no: int, label: ?string, performances: list<array{performance_id: string, song_id: string, song_title: string, order_no: int, is_display: bool, song_is_display: bool, co_vocalists: list<array{person_id: string, name: string, credit_name: ?string, order_no: int}>}>}>
     */
    private static function setlist(mixed $value): array
    {
        $result = [];
        foreach (self::asArray($value) as $item) {
            $itemData = self::asArray($item);
            $result[] = [
                'setlist_item_id' => self::stringValue(self::at($itemData, 'setlistItemId')) ?: self::uuid(),
                'order_no' => self::intValue(self::at($itemData, 'orderNo'), count($result) + 1),
                'label' => self::nullableString(self::at($itemData, 'label')),
                'performances' => self::performances(self::at($itemData, 'performances')),
            ];
        }

        return $result;
    }

    /**
     * @return list<array{person_id: string, name: string, credit_name: ?string, order_no: int}>
     */
    private static function coVocalists(mixed $value): array
    {
        $result = [];
        foreach (self::asArray($value) as $person) {
            $personData = self::asArray($person);
            $result[] = [
                'person_id' => self::stringValue(self::at($personData, 'personId')),
                'name' => self::stringValue(self::at($personData, 'name')),
                'credit_name' => self::nullableString(self::at($personData, 'creditName')),
                'order_no' => self::intValue(self::at($personData, 'orderNo'), count($result) + 1),
            ];
        }

        return $result;
    }

    /**
     * @return list<array{venue_id: string, name: string, kind: int, order_no: int}>
     */
    private static function venueIds(mixed $value): array
    {
        $result = [];
        foreach (self::asArray($value) as $id) {
            $result[] = ['venue_id' => self::stringValue($id), 'name' => '', 'kind' => 0, 'order_no' => count($result) + 1];
        }

        return $result;
    }

    /**
     * @return list<array{media_id: string, title: string, url: string, published_at: string, type: int, is_display: bool, order_no: int}>
     */
    private static function mediaIds(mixed $value): array
    {
        $result = [];
        foreach (self::asArray($value) as $id) {
            $result[] = ['media_id' => self::stringValue($id), 'title' => '', 'url' => '', 'published_at' => '', 'type' => 0, 'is_display' => true, 'order_no' => count($result) + 1];
        }

        return $result;
    }

    private static function uuid(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
    }
}
