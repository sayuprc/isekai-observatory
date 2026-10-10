<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Public\V1\Event;

use DateTime;
use Event\Application\Public\Query\CoVocalistItem;
use Event\Application\Public\Query\EventListItem;
use Event\Application\Public\Query\EventSourceItem;
use Event\Application\Public\Query\PerformanceItem;
use Event\Application\Public\Query\SetlistItem;
use Event\Application\Public\UseCase\List\ListOutputData;
use Illuminate\Http\JsonResponse;
use OpenAPI\Public\Client\Model\CoVocalist;
use OpenAPI\Public\Client\Model\Event;
use OpenAPI\Public\Client\Model\EventListResponse;
use OpenAPI\Public\Client\Model\EventSource;
use OpenAPI\Public\Client\Model\EventStatusValue;
use OpenAPI\Public\Client\Model\EventTypeValue;
use OpenAPI\Public\Client\Model\IsekaiObservatoryPackagesEventEventSchedule;
use OpenAPI\Public\Client\Model\SetlistItem as OpenApiSetlistItem;
use OpenAPI\Public\Client\Model\SongPerformance;

class ListPresenter
{
    public function present(ListOutputData $outputData): JsonResponse
    {
        return response()->json(
            new EventListResponse(['next_page_token' => $outputData->nextPageToken])
                ->setItems(array_map($this->toOpenApiEvent(...), $outputData->events)),
            200,
        );
    }

    private function toOpenApiEvent(EventListItem $event): Event
    {
        return new Event()
            ->setEventId($event->eventId)
            ->setTitle($event->title)
            ->setDescription($event->description)
            ->setType(EventTypeValue::from($event->type->value))
            ->setSchedule(new IsekaiObservatoryPackagesEventEventSchedule([
                'start_on' => $event->startOn === null ? null : new DateTime($event->startOn),
                'end_on' => $event->endOn === null ? null : new DateTime($event->endOn),
            ]))
            ->setStatus(EventStatusValue::from($event->status->value))
            ->setVenueIds($event->venueIds)
            ->setMediaIds($event->mediaIds)
            ->setReleaseIds($event->releaseIds)
            ->setSources(array_map($this->toOpenApiSource(...), $event->sources))
            ->setPerformances(array_map($this->toOpenApiPerformance(...), $event->performances))
            ->setSetlist(array_map($this->toOpenApiSetlistItem(...), $event->setlist));
    }

    private function toOpenApiSource(EventSourceItem $source): EventSource
    {
        return new EventSource()
            ->setDisplayName($source->displayName)
            ->setUrl($source->url);
    }

    private function toOpenApiPerformance(PerformanceItem $performance): SongPerformance
    {
        return new SongPerformance([
            'performance_id' => $performance->performanceId,
            'song_id' => $performance->songId,
            'song_title' => $performance->songTitle,
            'co_vocalists' => array_map($this->toOpenApiCoVocalist(...), $performance->coVocalists),
        ]);
    }

    private function toOpenApiCoVocalist(CoVocalistItem $coVocalist): CoVocalist
    {
        return new CoVocalist([
            'person_id' => $coVocalist->personId,
            'credit_name' => $coVocalist->creditName,
            'person_group_id' => $coVocalist->personGroupId,
        ]);
    }

    private function toOpenApiSetlistItem(SetlistItem $item): OpenApiSetlistItem
    {
        return new OpenApiSetlistItem([
            'label' => $item->label,
            'performance_ids' => $item->performanceIds,
        ]);
    }
}
