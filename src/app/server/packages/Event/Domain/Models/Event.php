<?php

declare(strict_types=1);

namespace Event\Domain\Models;

use Event\Domain\Models\Media\EventMediaLinks;
use Event\Domain\Models\Performances\SongPerformances;
use Event\Domain\Models\Setlist\Setlist;
use Event\Domain\Models\Sources\EventSources;
use Event\Domain\Models\Venues\EventVenueLinks;

/**
 * @phpstan-import-type _songPerformanceInput from \Event\Domain\Models\Performances\SongPerformances
 * @phpstan-import-type _setlistItemInput from \Event\Domain\Models\Setlist\Setlist
 * @phpstan-import-type _eventSourceInput from \Event\Domain\Models\Sources\EventSources
 */
readonly class Event
{
    public function __construct(
        public EventId $eventId,
        public EventTitle $title,
        public EventDescription $description,
        public EventType $type,
        public EventSchedule $schedule,
        public EventStatus $status,
        public bool $isDisplay,
        public EventVenueLinks $venues,
        public EventMediaLinks $media,
        public EventSources $sources,
        public SongPerformances $performances,
        public Setlist $setlist,
    ) {
    }

    /**
     * @param list<array{venueId: string, orderNo: int}> $venues
     * @param list<array{mediaId: string, orderNo: int}> $media
     * @param list<_eventSourceInput>                    $sources
     * @param list<_songPerformanceInput>                $performances
     * @param list<_setlistItemInput>                    $setlist
     */
    public static function reconstruct(
        string $eventId,
        string $title,
        string $description,
        int $type,
        ?string $startOn,
        ?string $endOn,
        int $status,
        bool $isDisplay,
        array $venues = [],
        array $media = [],
        array $sources = [],
        array $performances = [],
        array $setlist = [],
    ): self {
        return new self(
            new EventId($eventId),
            new EventTitle($title),
            new EventDescription($description),
            EventType::from($type),
            EventSchedule::reconstruct($startOn, $endOn),
            EventStatus::from($status),
            $isDisplay,
            EventVenueLinks::reconstruct($venues),
            EventMediaLinks::reconstruct($media),
            EventSources::reconstruct($sources),
            SongPerformances::reconstruct($performances),
            Setlist::reconstruct($setlist),
        );
    }

    /**
     * @return array{event_id: string, title: string, description: string, type: int, start_on: ?string, end_on: ?string, status: int, is_display: bool, venues: list<array{venue_id: string, order_no: int}>, media: list<array{media_id: string, order_no: int}>, sources: list<array{name: string, url: string, order_no: int}>, performances: list<array{performance_id: string, song_id: string, order_no: int, co_vocalists: list<array{person_id: string, credit_name: ?string, order_no: int}>}>, setlist: list<array{setlist_item_id: string, order_no: int, label: ?string, performance_ids: list<string>}>}
     */
    public function toArray(): array
    {
        return [
            'event_id' => $this->eventId->value,
            'title' => $this->title->value,
            'description' => $this->description->value,
            'type' => $this->type->value,
            ...$this->schedule->toArray(),
            'status' => $this->status->value,
            'is_display' => $this->isDisplay,
            'venues' => $this->venues->toArray(),
            'media' => $this->media->toArray(),
            'sources' => $this->sources->toArray(),
            'performances' => $this->performances->toArray(),
            'setlist' => $this->setlist->toArray(),
        ];
    }
}
