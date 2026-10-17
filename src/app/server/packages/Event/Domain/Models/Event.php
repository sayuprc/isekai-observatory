<?php

declare(strict_types=1);

namespace Event\Domain\Models;

use Event\Domain\Models\Media\EventMediaLinks;
use Event\Domain\Models\Performances\SongPerformances;
use Event\Domain\Models\Releases\EventReleaseLinks;
use Event\Domain\Models\Setlist\Setlist;
use Event\Domain\Models\Sources\EventSources;
use Event\Domain\Models\Venues\EventVenueLinks;
use Support\Domain\ValueObjects\Note;
use Tag\Domain\Models\TagReferences;

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
        public Note $note,
        public EventType $type,
        public EventSchedule $schedule,
        public EventStatus $status,
        public bool $isDisplay,
        public EventVenueLinks $venues,
        public EventMediaLinks $media,
        public EventReleaseLinks $releases,
        public EventSources $sources,
        public SongPerformances $performances,
        public Setlist $setlist,
        public TagReferences $tags,
    ) {
    }

    /**
     * @param array<int, array{venueId: string, orderNo: int}>   $venues
     * @param array<int, array{mediaId: string, orderNo: int}>   $media
     * @param array<int, array{releaseId: string, orderNo: int}> $releases
     * @param array<int, _eventSourceInput>                      $sources
     * @param array<int, _songPerformanceInput>                  $performances
     * @param list<_setlistItemInput>                            $setlist
     * @param array<int, array{tagId: string}>                   $tags
     */
    public static function reconstruct(
        string $eventId,
        string $title,
        string $description,
        string $note,
        int $type,
        ?string $startOn,
        ?string $endOn,
        int $status,
        bool $isDisplay,
        array $venues,
        array $media,
        array $releases,
        array $sources,
        array $performances,
        array $setlist,
        array $tags,
    ): self {
        return new self(
            new EventId($eventId),
            new EventTitle($title),
            new EventDescription($description),
            new Note($note),
            EventType::from($type),
            EventSchedule::reconstruct($startOn, $endOn),
            EventStatus::from($status),
            $isDisplay,
            EventVenueLinks::reconstruct($venues),
            EventMediaLinks::reconstruct($media),
            EventReleaseLinks::reconstruct($releases),
            EventSources::reconstruct($sources),
            SongPerformances::reconstruct($performances),
            Setlist::reconstruct($setlist),
            TagReferences::reconstruct($tags),
        );
    }

    /**
     * @return array{event_id: string, title: string, description: string, note: string, type: int, start_on: ?string, end_on: ?string, status: int, is_display: bool, venues: list<array{venue_id: string, order_no: int}>, media: list<array{media_id: string, order_no: int}>, releases: list<array{release_id: string, order_no: int}>, sources: list<array{name: string, url: string, order_no: int}>, performances: list<array{performance_id: string, song_id: string, order_no: int, co_vocalists: list<array{person_id: string, credit_name: ?string, person_group_id: ?string, order_no: int}>}>, setlist: list<array{setlist_item_id: string, order_no: int, label: ?string, performance_ids: list<string>}>, tags: list<array{tag_id: string}>}
     */
    public function toArray(): array
    {
        return [
            'event_id' => $this->eventId->value,
            'title' => $this->title->value,
            'description' => $this->description->value,
            'note' => $this->note->value,
            'type' => $this->type->value,
            ...$this->schedule->toArray(),
            'status' => $this->status->value,
            'is_display' => $this->isDisplay,
            'venues' => $this->venues->toArray(),
            'media' => $this->media->toArray(),
            'releases' => $this->releases->toArray(),
            'sources' => $this->sources->toArray(),
            'performances' => $this->performances->toArray(),
            'setlist' => $this->setlist->toArray(),
            'tags' => $this->tags->toArray(),
        ];
    }
}
