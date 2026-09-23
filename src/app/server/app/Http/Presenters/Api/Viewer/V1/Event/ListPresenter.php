<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Viewer\V1\Event;

use DateTime;
use Event\Application\Viewer\UseCase\List\ListOutputData;
use Event\Domain\Models\Event;
use Illuminate\Http\JsonResponse;
use Media\Domain\Models\MediaType;
use OpenAPI\Viewer\Client\Model\Event as OpenApiEvent;
use OpenAPI\Viewer\Client\Model\EventListResponse;
use OpenAPI\Viewer\Client\Model\EventMediaSummary as OpenApiEventMediaSummary;
use OpenAPI\Viewer\Client\Model\EventSource as OpenApiEventSource;
use OpenAPI\Viewer\Client\Model\EventStatusValue;
use OpenAPI\Viewer\Client\Model\EventTypeValue;
use OpenAPI\Viewer\Client\Model\EventVenueSummary as OpenApiEventVenueSummary;
use OpenAPI\Viewer\Client\Model\IsekaiObservatoryPackagesEventEventSchedule as OpenApiEventSchedule;
use OpenAPI\Viewer\Client\Model\MediaType as OpenApiMediaType;
use OpenAPI\Viewer\Client\Model\MediaTypeValue;
use OpenAPI\Viewer\Client\Model\PerformancePerson as OpenApiPerformancePerson;
use OpenAPI\Viewer\Client\Model\SetlistItem as OpenApiSetlistItem;
use OpenAPI\Viewer\Client\Model\SongPerformance as OpenApiSongPerformance;
use Venue\Domain\Models\VenueKind;

class ListPresenter
{
    public function present(ListOutputData $outputData): JsonResponse
    {
        return response()->json(
            new EventListResponse(['next_cursor' => $outputData->nextCursor])
                ->setEvents(array_map($this->toOpenApiEvent(...), $outputData->events)),
            200,
        );
    }

    private function toOpenApiEvent(Event $event): OpenApiEvent
    {
        $publicMedia = array_values(array_filter($event->media, static fn (array $media): bool => $media['is_display']));

        return new OpenApiEvent()
            ->setEventId($event->eventId)
            ->setTitle($event->title)
            ->setDescription($event->description)
            ->setTypeValue(EventTypeValue::from($event->type->value))
            ->setSchedule(new OpenApiEventSchedule([
                'start_on' => is_null($event->startOn) ? null : new DateTime($event->startOn),
                'end_on' => is_null($event->endOn) ? null : new DateTime($event->endOn),
            ]))
            ->setStatusValue(EventStatusValue::from($event->status->value))
            ->setVenues(array_map($this->toOpenApiVenue(...), $event->venues))
            ->setMedia(array_map($this->toOpenApiMedia(...), $publicMedia))
            ->setSources(array_map($this->toOpenApiSource(...), $event->sources))
            ->setPerformances(array_map($this->toOpenApiPerformance(...), $event->performances))
            ->setSetlist(array_map($this->toOpenApiSetlistItem(...), $event->setlist));
    }

    /** @param array{venue_id: string, name: string, kind: int, order_no: int} $venue */
    private function toOpenApiVenue(array $venue): OpenApiEventVenueSummary
    {
        return new OpenApiEventVenueSummary()
            ->setVenueId($venue['venue_id'])
            ->setName($venue['name'])
            ->setKindName(VenueKind::from($venue['kind'])->getName());
    }

    /** @param array{media_id: string, title: string, url: string, published_at: string, type: int, is_display: bool, order_no: int} $media */
    private function toOpenApiMedia(array $media): OpenApiEventMediaSummary
    {
        $type = MediaType::from($media['type']);

        return new OpenApiEventMediaSummary()
            ->setMediaId($media['media_id'])
            ->setTitle($media['title'])
            ->setUrl($media['url'])
            ->setPublishedAt(new DateTime($media['published_at']))
            ->setType(new OpenApiMediaType()->setName($type->getName())->setValue(MediaTypeValue::from($type->value)));
    }

    /** @param array{name: string, url: string, order_no: int} $source */
    private function toOpenApiSource(array $source): OpenApiEventSource
    {
        return new OpenApiEventSource()
            ->setDisplayName($source['name'])
            ->setUrl($source['url'])
            ->setOrderNo($source['order_no']);
    }

    /** @param array{performance_id: string, song_id: string, song_title: string, order_no: int, song_is_display: bool, co_vocalists: list<array{person_id: string, name: string, credit_name: ?string, order_no: int}>} $performance */
    private function toOpenApiPerformance(array $performance): OpenApiSongPerformance
    {
        return new OpenApiSongPerformance(['song_id' => $performance['song_is_display'] ? $performance['song_id'] : null])
            ->setPerformanceId($performance['performance_id'])
            ->setSongTitle($performance['song_title'])
            ->setCoVocalists(array_map(
                static fn (array $person): OpenApiPerformancePerson => new OpenApiPerformancePerson(['credit_name' => $person['credit_name']])
                    ->setPersonId($person['person_id'])
                    ->setName($person['name'])
                    ->setOrderNo($person['order_no']),
                $performance['co_vocalists'],
            ));
    }

    /** @param array{setlist_item_id: string, order_no: int, label: ?string, performances: list<array{performance_id: string, song_id: string, song_title: string, order_no: int, song_is_display: bool, co_vocalists: list<array{person_id: string, name: string, credit_name: ?string, order_no: int}>}>} $item */
    private function toOpenApiSetlistItem(array $item): OpenApiSetlistItem
    {
        return new OpenApiSetlistItem(['label' => $item['label']])
            ->setOrderNo($item['order_no'])
            ->setPerformances(array_map($this->toOpenApiPerformance(...), $item['performances']));
    }
}
