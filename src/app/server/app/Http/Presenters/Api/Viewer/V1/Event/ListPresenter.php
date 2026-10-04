<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Viewer\V1\Event;

use DateTime;
use Event\Application\Viewer\Query\EventCoVocalistSummary;
use Event\Application\Viewer\Query\EventListItem;
use Event\Application\Viewer\Query\EventMediaSummary;
use Event\Application\Viewer\Query\EventPerformanceSummary;
use Event\Application\Viewer\Query\EventReleaseSummary;
use Event\Application\Viewer\Query\EventSetlistItemSummary;
use Event\Application\Viewer\Query\EventSourceSummary;
use Event\Application\Viewer\Query\EventVenueSummary;
use Event\Application\Viewer\UseCase\List\ListOutputData;
use Illuminate\Http\JsonResponse;
use OpenAPI\Viewer\Client\Model\Event as OpenApiEvent;
use OpenAPI\Viewer\Client\Model\EventListResponse;
use OpenAPI\Viewer\Client\Model\EventMediaSummary as OpenApiEventMediaSummary;
use OpenAPI\Viewer\Client\Model\EventReleaseSummary as OpenApiEventReleaseSummary;
use OpenAPI\Viewer\Client\Model\EventSource as OpenApiEventSource;
use OpenAPI\Viewer\Client\Model\EventStatus as OpenApiEventStatus;
use OpenAPI\Viewer\Client\Model\EventStatusValue;
use OpenAPI\Viewer\Client\Model\EventType as OpenApiEventType;
use OpenAPI\Viewer\Client\Model\EventTypeValue;
use OpenAPI\Viewer\Client\Model\EventVenueSummary as OpenApiEventVenueSummary;
use OpenAPI\Viewer\Client\Model\IsekaiObservatoryPackagesEventEventSchedule as OpenApiEventSchedule;
use OpenAPI\Viewer\Client\Model\MediaType as OpenApiMediaType;
use OpenAPI\Viewer\Client\Model\MediaTypeValue;
use OpenAPI\Viewer\Client\Model\PerformancePerson as OpenApiPerformancePerson;
use OpenAPI\Viewer\Client\Model\PerformancePersonGroup as OpenApiPerformancePersonGroup;
use OpenAPI\Viewer\Client\Model\ReleaseFormat as OpenApiReleaseFormat;
use OpenAPI\Viewer\Client\Model\ReleaseFormatValue;
use OpenAPI\Viewer\Client\Model\SetlistItem as OpenApiSetlistItem;
use OpenAPI\Viewer\Client\Model\SongPerformance as OpenApiSongPerformance;
use Release\Domain\Models\ReleaseFormat;

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

    private function toOpenApiEvent(EventListItem $event): OpenApiEvent
    {
        return new OpenApiEvent()
            ->setEventId($event->eventId)
            ->setTitle($event->title)
            ->setDescription($event->description)
            ->setType(new OpenApiEventType()->setName($event->type->getName())->setValue(EventTypeValue::from($event->type->value)))
            ->setSchedule(new OpenApiEventSchedule([
                'start_on' => $event->startOn === null ? null : new DateTime($event->startOn),
                'end_on' => $event->endOn === null ? null : new DateTime($event->endOn),
            ]))
            ->setStatus(new OpenApiEventStatus()->setName($event->status->getName())->setValue(EventStatusValue::from($event->status->value)))
            ->setVenues(array_map($this->toOpenApiVenue(...), $event->venues))
            ->setMedia(array_map($this->toOpenApiMedia(...), $event->media))
            ->setReleases(array_map($this->toOpenApiRelease(...), $event->releases))
            ->setSources(array_map($this->toOpenApiSource(...), $event->sources))
            ->setPerformances(array_map($this->toOpenApiPerformance(...), $event->performances))
            ->setSetlist(array_map($this->toOpenApiSetlistItem(...), $event->setlist));
    }

    private function toOpenApiVenue(EventVenueSummary $venue): OpenApiEventVenueSummary
    {
        return new OpenApiEventVenueSummary()
            ->setVenueId($venue->venueId)
            ->setName($venue->name)
            ->setKindName($venue->kind->getName());
    }

    private function toOpenApiMedia(EventMediaSummary $media): OpenApiEventMediaSummary
    {
        return new OpenApiEventMediaSummary()
            ->setMediaId($media->mediaId)
            ->setTitle($media->title)
            ->setUrl($media->url)
            ->setPublishedAt(DateTime::createFromImmutable($media->publishedAt))
            ->setType(new OpenApiMediaType()->setName($media->type->getName())->setValue(MediaTypeValue::from($media->type->value)));
    }

    private function toOpenApiRelease(EventReleaseSummary $release): OpenApiEventReleaseSummary
    {
        return new OpenApiEventReleaseSummary()
            ->setReleaseId($release->releaseId)
            ->setReleaseGroupId($release->releaseGroupId)
            ->setReleaseGroupTitle($release->releaseGroupTitle)
            ->setName($release->name)
            ->setReleasedOn(new DateTime($release->releasedOn))
            ->setFormats(array_map(
                static fn (ReleaseFormat $format): OpenApiReleaseFormat => new OpenApiReleaseFormat()
                    ->setName($format->getName())
                    ->setValue(ReleaseFormatValue::from($format->value)),
                $release->formats,
            ));
    }

    private function toOpenApiSource(EventSourceSummary $source): OpenApiEventSource
    {
        return new OpenApiEventSource()
            ->setDisplayName($source->displayName)
            ->setUrl($source->url)
            ->setOrderNo($source->orderNo);
    }

    private function toOpenApiPerformance(EventPerformanceSummary $performance): OpenApiSongPerformance
    {
        return new OpenApiSongPerformance(['song_id' => $performance->songId])
            ->setPerformanceId($performance->performanceId)
            ->setSongTitle($performance->songTitle)
            ->setCoVocalists(array_map(
                static fn (EventCoVocalistSummary $coVocalist): OpenApiPerformancePerson => new OpenApiPerformancePerson([
                    'credit_name' => $coVocalist->creditName,
                    'person_group' => $coVocalist->personGroup === null ? null : new OpenApiPerformancePersonGroup()
                        ->setPersonGroupId($coVocalist->personGroup->personGroupId)
                        ->setName($coVocalist->personGroup->name),
                ])
                    ->setPersonId($coVocalist->personId)
                    ->setName($coVocalist->name)
                    ->setOrderNo($coVocalist->orderNo),
                $performance->coVocalists,
            ));
    }

    private function toOpenApiSetlistItem(EventSetlistItemSummary $item): OpenApiSetlistItem
    {
        return new OpenApiSetlistItem(['label' => $item->label])
            ->setOrderNo($item->orderNo)
            ->setPerformances(array_map($this->toOpenApiPerformance(...), $item->performances));
    }
}
