<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Viewer\V1\Event;

use DateTimeImmutable;
use DateTimeZone;
use Event\Application\Viewer\UseCase\List\ListOutputData;
use Event\Domain\Models\Event;
use Illuminate\Http\JsonResponse;
use Media\Domain\Models\MediaType;

class ListPresenter
{
    public function present(ListOutputData $outputData): JsonResponse
    {
        $response = ['events' => array_map($this->toEvent(...), $outputData->events)];
        if ($outputData->nextCursor !== null) {
            $response['nextCursor'] = $outputData->nextCursor;
        }

        return response()->json($response);
    }

    /** @return array<string, mixed> */
    private function toEvent(Event $event): array
    {
        return [
            'eventId' => $event->eventId, 'title' => $event->title, 'description' => $event->description, 'typeValue' => $event->type->value,
            'schedule' => $this->toSchedule($event), 'statusValue' => $event->status->value,
            'venues' => array_map(static fn (array $venue): array => ['venueId' => $venue['venue_id'], 'name' => $venue['name'], 'kindName' => $venue['kind'] === 2 ? '配信' : '現地'], $event->venues),
            'media' => array_values(array_filter(array_map(static function (array $media): ?array {
                if (! $media['is_display']) {
                    return null;
                }
                $type = MediaType::from($media['type']);

                return ['mediaId' => $media['media_id'], 'title' => $media['title'], 'url' => $media['url'], 'publishedAt' => new DateTimeImmutable($media['published_at'])->setTimezone(new DateTimeZone(date_default_timezone_get()))->format(DATE_ATOM), 'type' => ['name' => $type->getName(), 'value' => $type->value]];
            }, $event->media))),
            'sources' => array_map(static fn (array $source): array => ['displayName' => $source['name'], 'url' => $source['url'], 'orderNo' => $source['order_no']], $event->sources),
            'performances' => array_map($this->toPerformance(...), $event->performances),
            'setlist' => array_map(fn (array $item): array => ['orderNo' => $item['order_no'], 'label' => $item['label'], 'performances' => array_map($this->toPerformance(...), $item['performances'])], $event->setlist),
        ];
    }

    /**
     * @param array{performance_id: string, song_id: string, song_title: string, order_no: int, song_is_display: bool, co_vocalists: list<array{person_id: string, name: string, credit_name: ?string, order_no: int}>} $performance
     *
     * @return array{performanceId: string, songId: ?string, songTitle: string, coVocalists: list<array{personId: string, name: string, creditName: ?string, orderNo: int}>}
     */
    private function toPerformance(array $performance): array
    {
        return ['performanceId' => $performance['performance_id'], 'songId' => $performance['song_is_display'] ? $performance['song_id'] : null, 'songTitle' => $performance['song_title'], 'coVocalists' => array_map(static fn (array $person): array => ['personId' => $person['person_id'], 'name' => $person['name'], 'creditName' => $person['credit_name'], 'orderNo' => $person['order_no']], $performance['co_vocalists'])];
    }

    /** @return array{startOn: ?string, endOn: ?string} */
    private function toSchedule(Event $event): array
    {
        return $event->schedule();
    }
}
