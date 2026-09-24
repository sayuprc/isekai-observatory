<?php

declare(strict_types=1);

namespace Tests\Integration\Event\Infrastructures;

use Event\Domain\Models\EventId;
use Event\Domain\Models\EventStatus;
use Event\Infrastructures\EventRepository;
use Media\Domain\Models\MediaType;
use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\SongType;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;
use Venue\Domain\Models\VenueKind;

class EventRepositoryTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function saveAndFindWithChildren(): void
    {
        $songId = $this->generateUuid();
        $personId = $this->generateUuid();
        $venueId = $this->generateUuid();
        $mediaId = $this->generateUuid();
        $this->storeSongs($this->createSong($songId, '披露曲', '説明', SongType::Original, true, 1));
        $this->storePersons($this->createPerson($personId, '共演者', 1));
        $this->storeVenues($this->createVenue($venueId, '会場', VenueKind::Physical));
        $this->storeMedia($this->createMedia($mediaId, '配信アーカイブ', 'https://example.com/archive', MediaType::LiveStream, true));

        $eventId = $this->generateUuid();
        $performanceId = $this->generateUuid();
        $setlistItemId = $this->generateUuid();
        $event = $this->createEvent(
            $eventId,
            venues: [['venueId' => $venueId, 'orderNo' => 1]],
            media: [['mediaId' => $mediaId, 'orderNo' => 1]],
            sources: [['displayName' => '公式', 'url' => 'https://example.com/live', 'orderNo' => 1]],
            performances: [['performanceId' => $performanceId, 'songId' => $songId, 'orderNo' => 1, 'coVocalists' => [['personId' => $personId, 'creditName' => 'ゲスト', 'orderNo' => 1]]]],
            setlist: [['setlistItemId' => $setlistItemId, 'orderNo' => 1, 'label' => '本編', 'performanceIds' => [$performanceId]]],
        );
        $this->getInstance()->save($event);

        $this->assertEquals($event, $this->getInstance()->find(new EventId($eventId)));
    }

    #[Test]
    public function saveAndFindGroupsChildrenByParent(): void
    {
        $songId = $this->generateUuid();
        $person1 = $this->generateUuid();
        $person2 = $this->generateUuid();
        $this->storeSongs($this->createSong($songId, '披露曲', '説明', SongType::Original, true, 1));
        $this->storePersons($this->createPerson($person1, '共演者1', 1), $this->createPerson($person2, '共演者2', 2));

        $eventId = $this->generateUuid();
        $performance1 = $this->generateUuid();
        $performance2 = $this->generateUuid();
        $performance3 = $this->generateUuid();
        $event = $this->createEvent(
            $eventId,
            performances: [
                ['performanceId' => $performance1, 'songId' => $songId, 'orderNo' => 1, 'coVocalists' => [
                    ['personId' => $person2, 'creditName' => null, 'orderNo' => 1],
                    ['personId' => $person1, 'creditName' => 'ユニット', 'orderNo' => 2],
                ]],
                ['performanceId' => $performance2, 'songId' => $songId, 'orderNo' => 2, 'coVocalists' => []],
                ['performanceId' => $performance3, 'songId' => $songId, 'orderNo' => 3, 'coVocalists' => [
                    ['personId' => $person1, 'creditName' => null, 'orderNo' => 1],
                ]],
            ],
            setlist: [
                ['setlistItemId' => $this->generateUuid(), 'orderNo' => 1, 'label' => 'メドレー', 'performanceIds' => [$performance3, $performance1]],
                ['setlistItemId' => $this->generateUuid(), 'orderNo' => 2, 'label' => 'MC', 'performanceIds' => []],
                ['setlistItemId' => $this->generateUuid(), 'orderNo' => 3, 'label' => null, 'performanceIds' => [$performance2]],
            ],
        );
        $this->getInstance()->save($event);

        $this->assertEquals($event, $this->getInstance()->find(new EventId($eventId)));
    }

    #[Test]
    public function saveReplacesChildren(): void
    {
        $songId = $this->generateUuid();
        $personId = $this->generateUuid();
        $this->storeSongs($this->createSong($songId, '披露曲', '説明', SongType::Original, true, 1));
        $this->storePersons($this->createPerson($personId, '共演者', 1));

        $eventId = $this->generateUuid();
        $performanceId = $this->generateUuid();
        $this->getInstance()->save($this->createEvent(
            $eventId,
            performances: [['performanceId' => $performanceId, 'songId' => $songId, 'orderNo' => 1, 'coVocalists' => [
                ['personId' => $personId, 'creditName' => null, 'orderNo' => 1],
            ]]],
            setlist: [['setlistItemId' => $this->generateUuid(), 'orderNo' => 1, 'label' => null, 'performanceIds' => [$performanceId]]],
        ));
        $updated = $this->createEvent($eventId, title: '中止されたライブ', status: EventStatus::Cancelled);
        $this->getInstance()->save($updated);

        $this->assertEquals($updated, $this->getInstance()->find(new EventId($eventId)));
        $this->assertDatabaseCount('events', 1);
        $this->assertDatabaseCount('song_performances', 0);
        $this->assertDatabaseCount('song_performance_persons', 0);
        $this->assertDatabaseCount('event_setlist_items', 0);
        $this->assertDatabaseCount('event_setlist_item_performances', 0);
    }

    #[Test]
    public function findNotFound(): void
    {
        $this->assertNull($this->getInstance()->find(new EventId($this->generateUuid())));
    }

    #[Test]
    public function deletingRemovesOwnedChildren(): void
    {
        $songId = $this->generateUuid();
        $this->storeSongs($this->createSong($songId, '披露曲', '説明', SongType::Original, true, 1));

        $eventId = $this->generateUuid();
        $performanceId = $this->generateUuid();
        $this->getInstance()->save($this->createEvent(
            $eventId,
            sources: [['displayName' => '公式', 'url' => 'https://example.com/live', 'orderNo' => 1]],
            performances: [['performanceId' => $performanceId, 'songId' => $songId, 'orderNo' => 1, 'coVocalists' => []]],
            setlist: [['setlistItemId' => $this->generateUuid(), 'orderNo' => 1, 'label' => null, 'performanceIds' => [$performanceId]]],
        ));

        $this->getInstance()->delete(new EventId($eventId));

        $this->assertNull($this->getInstance()->find(new EventId($eventId)));
        $this->assertDatabaseCount('event_sources', 0);
        $this->assertDatabaseCount('song_performances', 0);
        $this->assertDatabaseCount('event_setlist_items', 0);
    }

    private function getInstance(): EventRepository
    {
        return $this->app->make(EventRepository::class);
    }
}
