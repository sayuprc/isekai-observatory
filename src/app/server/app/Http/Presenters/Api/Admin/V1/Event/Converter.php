<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Event;

use DateTime;
use Event\Application\Admin\Assemble\AssembledCoVocalist;
use Event\Application\Admin\Assemble\AssembledEvent;
use Event\Application\Admin\Assemble\AssembledMedia;
use Event\Application\Admin\Assemble\AssembledPerformance;
use Event\Application\Admin\Assemble\AssembledSetlistItem;
use Event\Application\Admin\Assemble\AssembledSource;
use Event\Application\Admin\Assemble\AssembledVenue;
use Event\Domain\Models\Event;
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

class Converter
{
    public function toOpenApiEventSummary(Event $event): OpenApiEventSummary
    {
        return new OpenApiEventSummary()
            ->setEventId($event->eventId->value)
            ->setTitle($event->title->value)
            ->setTypeValue(EventTypeValue::from($event->type->value))
            ->setSchedule(new OpenApiEventSchedule([
                'start_on' => is_null($event->schedule->startOn) ? null : DateTime::createFromImmutable($event->schedule->startOn->value),
                'end_on' => is_null($event->schedule->endOn) ? null : DateTime::createFromImmutable($event->schedule->endOn->value),
            ]))
            ->setStatusValue(EventStatusValue::from($event->status->value))
            ->setIsDisplay($event->isDisplay);
    }

    public function toOpenApiEvent(AssembledEvent $event): OpenApiEvent
    {
        return new OpenApiEvent()
            ->setEventId($event->eventId)
            ->setTitle($event->title)
            ->setDescription($event->description)
            ->setTypeValue(EventTypeValue::from($event->typeValue))
            ->setSchedule(new OpenApiEventSchedule([
                'start_on' => is_null($event->startOn) ? null : new DateTime($event->startOn),
                'end_on' => is_null($event->endOn) ? null : new DateTime($event->endOn),
            ]))
            ->setStatusValue(EventStatusValue::from($event->statusValue))
            ->setIsDisplay($event->isDisplay)
            ->setVenues(array_map($this->toOpenApiVenue(...), $event->venues))
            ->setMedia(array_map($this->toOpenApiMedia(...), $event->media))
            ->setSources(array_map($this->toOpenApiSource(...), $event->sources))
            ->setPerformances(array_map($this->toOpenApiPerformance(...), $event->performances))
            ->setSetlist(array_map($this->toOpenApiSetlistItem(...), $event->setlist));
    }

    private function toOpenApiVenue(AssembledVenue $venue): OpenApiVenue
    {
        return new OpenApiVenue()
            ->setVenueId($venue->venueId)
            ->setName($venue->name)
            ->setKind(
                new OpenApiVenueKind()
                    ->setName($venue->kindName)
                    ->setValue(VenueKindValue::from($venue->kindValue)),
            );
    }

    private function toOpenApiMedia(AssembledMedia $media): OpenApiMedia
    {
        return new OpenApiMedia()
            ->setMediaId($media->mediaId)
            ->setTitle($media->title)
            ->setUrl($media->url)
            ->setPublishedAt(DateTime::createFromImmutable($media->publishedAt))
            ->setType(
                new OpenApiMediaType()
                    ->setName($media->typeName)
                    ->setValue(MediaTypeValue::from($media->typeValue)),
            )
            ->setIsDisplay($media->isDisplay);
    }

    private function toOpenApiSource(AssembledSource $source): OpenApiEventSource
    {
        return new OpenApiEventSource()
            ->setDisplayName($source->displayName)
            ->setUrl($source->url)
            ->setOrderNo($source->orderNo);
    }

    private function toOpenApiPerformance(AssembledPerformance $performance): OpenApiSongPerformance
    {
        return new OpenApiSongPerformance()
            ->setPerformanceId($performance->performanceId)
            ->setSongId($performance->songId)
            ->setSongTitle($performance->songTitle)
            ->setOrderNo($performance->orderNo)
            ->setCoVocalists(array_map(
                static fn (AssembledCoVocalist $coVocalist): OpenApiPerformancePerson => new OpenApiPerformancePerson(['credit_name' => $coVocalist->creditName])
                    ->setPersonId($coVocalist->personId)
                    ->setName($coVocalist->name)
                    ->setOrderNo($coVocalist->orderNo),
                $performance->coVocalists,
            ));
    }

    private function toOpenApiSetlistItem(AssembledSetlistItem $item): OpenApiSetlistItem
    {
        return new OpenApiSetlistItem(['label' => $item->label])
            ->setSetlistItemId($item->setlistItemId)
            ->setOrderNo($item->orderNo)
            ->setPerformances(array_map($this->toOpenApiPerformance(...), $item->performances));
    }
}
