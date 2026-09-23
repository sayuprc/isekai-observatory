<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Viewer\V1\Event;

use Event\Domain\Models\Event;
use Event\Route\ViewerEventRouteMap;
use Media\Domain\Models\MediaType;
use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\SongType;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;
use Venue\Domain\Models\VenueKind;

class ListEventTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function hidesPrivateRelationsWithoutDroppingPerformanceHistory(): void
    {
        $visibleSongId = $this->generateUuid();
        $hiddenSongId = $this->generateUuid();
        $personId = $this->generateUuid();
        $venueId = $this->generateUuid();
        $visibleMediaId = $this->generateUuid();
        $hiddenMediaId = $this->generateUuid();
        $eventId = $this->generateUuid();
        $visiblePerformanceId = $this->generateUuid();
        $hiddenPerformanceId = $this->generateUuid();

        $this->storeSongs(
            $this->createSong($visibleSongId, '公開楽曲', '説明', SongType::Original, true, 1),
            $this->createSong($hiddenSongId, '非公開楽曲', '説明', SongType::Original, false, 2),
        );
        $this->storePersons($this->createPerson($personId, '共演者', 1));
        $this->storeVenues($this->createVenue($venueId, '会場', VenueKind::Physical));
        $this->storeMedia(
            $this->createMedia($visibleMediaId, '公開メディア', 'https://example.com/public', MediaType::Mv, true),
            $this->createMedia($hiddenMediaId, '非公開メディア', 'https://example.com/private', MediaType::Mv, false),
        );

        $this->storeEvents($this->event(
            $eventId,
            $visibleSongId,
            $hiddenSongId,
            $visiblePerformanceId,
            $hiddenPerformanceId,
            $venueId,
            $visibleMediaId,
            $hiddenMediaId,
            $personId,
        ));

        $this->get(route(ViewerEventRouteMap::List, ['limit' => 1]))
            ->assertStatus(200)
            ->assertJsonCount(1, 'events')
            ->assertJsonPath('events.0.eventId', $eventId)
            ->assertJsonPath('events.0.statusValue', 1)
            ->assertJsonPath('events.0.venues.0.name', '会場')
            ->assertJsonPath('events.0.media.0.mediaId', $visibleMediaId)
            ->assertJsonCount(1, 'events.0.media')
            ->assertJsonPath('events.0.performances.0.songId', $visibleSongId)
            ->assertJsonPath('events.0.performances.1.songId', null)
            ->assertJsonPath('events.0.performances.1.songTitle', '非公開楽曲')
            ->assertJsonPath('events.0.performances.0.coVocalists.0.name', '共演者');
    }

    #[Test]
    public function formatsDateRangeScheduleForPublicContract(): void
    {
        $eventId = $this->generateUuid();
        $this->storeEvents(Event::fromInput($eventId, [
            'title' => '日時付き活動',
            'description' => '',
            'typeValue' => 2,
            'schedule' => ['startOn' => '2026-10-01', 'endOn' => '2026-10-03'],
            'statusValue' => 1,
            'isDisplay' => true,
            'venueIds' => [],
            'mediaIds' => [],
            'sources' => [],
            'performances' => [],
            'setlist' => [],
        ]));

        $this->get(route(ViewerEventRouteMap::List, ['limit' => 1]))
            ->assertStatus(200)
            ->assertJsonPath('events.0.eventId', $eventId)
            ->assertJsonPath('events.0.schedule.startOn', '2026-10-01')
            ->assertJsonPath('events.0.schedule.endOn', '2026-10-03');
    }

    private function event(
        string $eventId,
        string $visibleSongId,
        string $hiddenSongId,
        string $visiblePerformanceId,
        string $hiddenPerformanceId,
        string $venueId,
        string $visibleMediaId,
        string $hiddenMediaId,
        string $personId,
    ): Event {
        return Event::fromInput($eventId, [
            'title' => '公開ライブ',
            'description' => '説明',
            'typeValue' => 1,
            'schedule' => ['startOn' => '2026-10-01', 'endOn' => null],
            'statusValue' => 1,
            'isDisplay' => true,
            'venueIds' => [$venueId],
            'mediaIds' => [$visibleMediaId, $hiddenMediaId],
            'sources' => [['displayName' => '公式', 'url' => 'https://example.com/event', 'orderNo' => 1]],
            'performances' => [
                ['performanceId' => $visiblePerformanceId, 'songId' => $visibleSongId, 'songTitle' => '公開楽曲', 'orderNo' => 1, 'coVocalists' => [['personId' => $personId, 'name' => '共演者', 'creditName' => null, 'orderNo' => 1]]],
                ['performanceId' => $hiddenPerformanceId, 'songId' => $hiddenSongId, 'songTitle' => '非公開楽曲', 'orderNo' => 2, 'coVocalists' => []],
            ],
            'setlist' => [['setlistItemId' => $this->generateUuid(), 'orderNo' => 1, 'label' => '本編', 'performances' => [['performanceId' => $visiblePerformanceId, 'songId' => $visibleSongId, 'songTitle' => '公開楽曲', 'orderNo' => 1, 'coVocalists' => []]]]],
        ]);
    }
}
