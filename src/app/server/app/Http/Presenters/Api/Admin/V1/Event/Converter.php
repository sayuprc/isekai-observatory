<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Event;

use DateTimeImmutable;
use DateTimeZone;
use Event\Domain\Models\Event;
use Media\Domain\Models\MediaType;
use Venue\Domain\Models\VenueKind;

class Converter
{
    /** @return array<string, mixed> */
    public function toSummary(Event $event): array
    {
        return ['eventId' => $event->eventId, 'title' => $event->title, 'typeValue' => $event->type->value, 'schedule' => $this->toSchedule($event), 'statusValue' => $event->status?->value, 'isDisplay' => $event->isDisplay];
    }

    /** @return array<string, mixed> */
    public function toEvent(Event $event): array
    {
        return [
            'eventId' => $event->eventId, 'title' => $event->title, 'description' => $event->description, 'typeValue' => $event->type->value,
            'schedule' => $this->toSchedule($event), 'statusValue' => $event->status?->value, 'postponedToEventId' => $event->postponedToEventId, 'isDisplay' => $event->isDisplay,
            'venues' => array_map(static function (array $venue): array {
                $kind = VenueKind::from($venue['kind']);

                return ['venueId' => $venue['venue_id'], 'name' => $venue['name'], 'kind' => ['name' => $kind->getName(), 'value' => $kind->value]];
            }, $event->venues),
            'media' => array_map(static function (array $media): array {
                $type = MediaType::from($media['type']);

                return ['mediaId' => $media['media_id'], 'title' => $media['title'], 'url' => $media['url'], 'publishedAt' => new DateTimeImmutable($media['published_at'])->setTimezone(new DateTimeZone(date_default_timezone_get()))->format(DATE_ATOM), 'type' => ['name' => $type->getName(), 'value' => $type->value], 'isDisplay' => $media['is_display']];
            }, $event->media),
            'sources' => array_map(static fn (array $source): array => ['displayName' => $source['name'], 'url' => $source['url'], 'orderNo' => $source['order_no']], $event->sources),
            'performances' => array_map($this->toPerformance(...), $event->performances),
            'setlist' => array_map(fn (array $item): array => ['setlistItemId' => $item['setlist_item_id'], 'orderNo' => $item['order_no'], 'label' => $item['label'], 'performances' => array_map($this->toPerformance(...), $item['performances'])], $event->setlist),
        ];
    }

    /**
     * @param array{performance_id: string, song_id: string, song_title: string, order_no: int, is_display: bool, song_is_display: bool, co_vocalists: list<array{person_id: string, name: string, credit_name: ?string, order_no: int}>} $performance
     *
     * @return array{performanceId: string, songId: string, songTitle: string, orderNo: int, isDisplay: bool, coVocalists: list<array{personId: string, name: string, creditName: ?string, orderNo: int}>}
     */
    private function toPerformance(array $performance): array
    {
        return ['performanceId' => $performance['performance_id'], 'songId' => $performance['song_id'], 'songTitle' => $performance['song_title'], 'orderNo' => $performance['order_no'], 'isDisplay' => $performance['is_display'], 'coVocalists' => array_map(static fn (array $person): array => ['personId' => $person['person_id'], 'name' => $person['name'], 'creditName' => $person['credit_name'], 'orderNo' => $person['order_no']], $performance['co_vocalists'])];
    }

    /** @return array{type: int, startDate: ?string, endDate: ?string, startDateTime: ?string, endDateTime: ?string, timeZone: ?string} */
    private function toSchedule(Event $event): array
    {
        $schedule = $event->schedule();
        foreach (['startDateTime', 'endDateTime'] as $key) {
            if ($schedule[$key] !== null) {
                $zone = $event->timeZone === null ? null : new DateTimeZone($event->timeZone);
                $schedule[$key] = new DateTimeImmutable($schedule[$key], $zone)->format(DATE_ATOM);
            }
        }

        return $schedule;
    }
}
