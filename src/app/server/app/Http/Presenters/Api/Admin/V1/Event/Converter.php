<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Event;

use DateTime;
use Event\Domain\Models\Event;
use Media\Domain\Models\MediaType;
use OpenAPI\Admin\Client\Model\Event as OpenApiEvent;
use OpenAPI\Admin\Client\Model\EventSource as OpenApiEventSource;
use OpenAPI\Admin\Client\Model\EventStatusValue;
use OpenAPI\Admin\Client\Model\EventSummary as OpenApiEventSummary;
use OpenAPI\Admin\Client\Model\EventTypeValue;
use OpenAPI\Admin\Client\Model\IsekaiObservatoryPackagesEventEventSchedule as OpenApiEventSchedule;
use OpenAPI\Admin\Client\Model\Media as OpenApiMedia;
use OpenAPI\Admin\Client\Model\MediaType as OpenApiMediaType;
use OpenAPI\Admin\Client\Model\MediaTypeValue;
use OpenAPI\Admin\Client\Model\PerformancePerson as OpenApiPerformancePerson;
use OpenAPI\Admin\Client\Model\SetlistItem as OpenApiSetlistItem;
use OpenAPI\Admin\Client\Model\SongPerformance as OpenApiSongPerformance;
use OpenAPI\Admin\Client\Model\Venue as OpenApiVenue;
use OpenAPI\Admin\Client\Model\VenueKind as OpenApiVenueKind;
use OpenAPI\Admin\Client\Model\VenueKindValue;
use Venue\Domain\Models\VenueKind;

class Converter
{
    public function toOpenApiEventSummary(Event $event): OpenApiEventSummary
    {
        return new OpenApiEventSummary()
            ->setEventId($event->eventId)
            ->setTitle($event->title)
            ->setTypeValue(EventTypeValue::from($event->type->value))
            ->setSchedule($this->toOpenApiSchedule($event))
            ->setStatusValue(EventStatusValue::from($event->status->value))
            ->setIsDisplay($event->isDisplay);
    }

    public function toOpenApiEvent(Event $event): OpenApiEvent
    {
        return new OpenApiEvent()
            ->setEventId($event->eventId)
            ->setTitle($event->title)
            ->setDescription($event->description)
            ->setTypeValue(EventTypeValue::from($event->type->value))
            ->setSchedule($this->toOpenApiSchedule($event))
            ->setStatusValue(EventStatusValue::from($event->status->value))
            ->setIsDisplay($event->isDisplay)
            ->setVenues(array_map($this->toOpenApiVenue(...), $event->venues))
            ->setMedia(array_map($this->toOpenApiMedia(...), $event->media))
            ->setSources(array_map($this->toOpenApiSource(...), $event->sources))
            ->setPerformances(array_map($this->toOpenApiPerformance(...), $event->performances))
            ->setSetlist(array_map($this->toOpenApiSetlistItem(...), $event->setlist));
    }

    private function toOpenApiSchedule(Event $event): OpenApiEventSchedule
    {
        return new OpenApiEventSchedule([
            'start_on' => is_null($event->startOn) ? null : new DateTime($event->startOn),
            'end_on' => is_null($event->endOn) ? null : new DateTime($event->endOn),
        ]);
    }

    /** @param array{venue_id: string, name: string, kind: int, order_no: int} $venue */
    private function toOpenApiVenue(array $venue): OpenApiVenue
    {
        $kind = VenueKind::from($venue['kind']);

        return new OpenApiVenue()
            ->setVenueId($venue['venue_id'])
            ->setName($venue['name'])
            ->setKind(
                new OpenApiVenueKind()
                    ->setName($kind->getName())
                    ->setValue(VenueKindValue::from($kind->value)),
            );
    }

    /** @param array{media_id: string, title: string, url: string, published_at: string, type: int, is_display: bool, order_no: int} $media */
    private function toOpenApiMedia(array $media): OpenApiMedia
    {
        $type = MediaType::from($media['type']);

        return new OpenApiMedia()
            ->setMediaId($media['media_id'])
            ->setTitle($media['title'])
            ->setUrl($media['url'])
            ->setPublishedAt(new DateTime($media['published_at']))
            ->setType(
                new OpenApiMediaType()
                    ->setName($type->getName())
                    ->setValue(MediaTypeValue::from($type->value)),
            )
            ->setIsDisplay($media['is_display']);
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
        return new OpenApiSongPerformance()
            ->setPerformanceId($performance['performance_id'])
            ->setSongId($performance['song_id'])
            ->setSongTitle($performance['song_title'])
            ->setOrderNo($performance['order_no'])
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
            ->setSetlistItemId($item['setlist_item_id'])
            ->setOrderNo($item['order_no'])
            ->setPerformances(array_map($this->toOpenApiPerformance(...), $item['performances']));
    }
}
