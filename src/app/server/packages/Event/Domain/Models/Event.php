<?php

declare(strict_types=1);

namespace Event\Domain\Models;

class Event
{
    /**
     * @param list<array{venue_id: string, name: string, kind: int, order_no: int}>                                                                                                                                                                                                                              $venues
     * @param list<array{media_id: string, title: string, url: string, published_at: string, type: int, is_display: bool, order_no: int}>                                                                                                                                                                        $media
     * @param list<array{name: string, url: string, order_no: int}>                                                                                                                                                                                                                                              $sources
     * @param list<array{performance_id: string, song_id: string, song_title: string, order_no: int, song_is_display: bool, co_vocalists: list<array{person_id: string, name: string, credit_name: ?string, order_no: int}>}>                                                                                    $performances
     * @param list<array{setlist_item_id: string, order_no: int, label: ?string, performances: list<array{performance_id: string, song_id: string, song_title: string, order_no: int, song_is_display: bool, co_vocalists: list<array{person_id: string, name: string, credit_name: ?string, order_no: int}>}>}> $setlist
     */
    public function __construct(
        public string $eventId,
        public string $title,
        public string $description,
        public EventType $type,
        public ?string $startOn,
        public ?string $endOn,
        public EventStatus $status,
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
            self::stringValue(self::at($data, 'description')),
            EventType::from(self::intValue(self::at($data, 'typeValue'))),
            self::nullableString(self::at($schedule, 'startOn')),
            self::nullableString(self::at($schedule, 'endOn')),
            EventStatus::from(self::intValue(self::at($data, 'statusValue'), -1)),
            self::boolValue(self::at($data, 'isDisplay')),
            self::venueIds(self::at($data, 'venueIds')),
            self::mediaIds(self::at($data, 'mediaIds')),
            self::sources(self::at($data, 'sources')),
            self::performances(self::at($data, 'performances')),
            self::setlist(self::at($data, 'setlist')),
        );
    }

    /** @return array{event_id: string, title: string, description: string, type: int, start_on: ?string, end_on: ?string, status: int, is_display: bool} */
    public function toArray(): array
    {
        return [
            'event_id' => $this->eventId,
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type->value,
            'start_on' => $this->startOn,
            'end_on' => $this->endOn,
            'status' => $this->status->value,
            'is_display' => $this->isDisplay,
        ];
    }

    /** @return array{startOn: ?string, endOn: ?string} */
    public function schedule(): array
    {
        return [
            'startOn' => $this->startOn,
            'endOn' => $this->endOn,
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
     * @return list<array{performance_id: string, song_id: string, song_title: string, order_no: int, song_is_display: bool, co_vocalists: list<array{person_id: string, name: string, credit_name: ?string, order_no: int}>}>
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
                'song_is_display' => true,
                'co_vocalists' => self::coVocalists(self::at($performanceData, 'coVocalists')),
            ];
        }

        return $result;
    }

    /**
     * @return list<array{setlist_item_id: string, order_no: int, label: ?string, performances: list<array{performance_id: string, song_id: string, song_title: string, order_no: int, song_is_display: bool, co_vocalists: list<array{person_id: string, name: string, credit_name: ?string, order_no: int}>}>}>
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
