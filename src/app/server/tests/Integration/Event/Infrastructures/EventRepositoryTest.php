<?php

declare(strict_types=1);

namespace Tests\Integration\Event\Infrastructures;

use Event\Domain\Criteria\EventSearchCriteria;
use Event\Domain\Criteria\Sort;
use Event\Domain\Models\EventId;
use Event\Domain\Models\EventStatus;
use Event\Domain\Models\EventType;
use Event\Infrastructures\EventRepository;
use Media\Domain\Models\MediaType;
use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\SongType;
use Support\Domain\SearchCriteria\Order;
use Support\Domain\SearchCriteria\PerPage;
use Support\Optional\None;
use Support\Optional\Some;
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
    public function saveReplacesChildren(): void
    {
        $songId = $this->generateUuid();
        $this->storeSongs($this->createSong($songId, '披露曲', '説明', SongType::Original, true, 1));

        $eventId = $this->generateUuid();
        $this->getInstance()->save($this->createEvent(
            $eventId,
            performances: [['performanceId' => $this->generateUuid(), 'songId' => $songId, 'orderNo' => 1, 'coVocalists' => []]],
        ));
        $updated = $this->createEvent($eventId, title: '中止されたライブ', status: EventStatus::Cancelled);
        $this->getInstance()->save($updated);

        $this->assertEquals($updated, $this->getInstance()->find(new EventId($eventId)));
        $this->assertDatabaseCount('events', 1);
        $this->assertDatabaseCount('song_performances', 0);
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

    #[Test]
    public function searchFiltersByStatusAndDisplay(): void
    {
        $normal = $this->createEvent($this->generateUuid());
        $this->getInstance()->save($normal);
        $this->getInstance()->save($this->createEvent($this->generateUuid(), status: EventStatus::Cancelled));
        $this->getInstance()->save($this->createEvent($this->generateUuid(), isDisplay: false));

        $criteria = new EventSearchCriteria(new None(), new Some(EventType::Live), new Some(EventStatus::Normal), new Some(true));

        $this->assertEquals([$normal], $this->getInstance()->search($criteria));
        $this->assertSame(1, $this->getInstance()->maxPage($criteria));
    }

    #[Test]
    public function searchByTitleSortedByTitleDesc(): void
    {
        $first = $this->createEvent($this->generateUuid(), title: 'あのライブ');
        $second = $this->createEvent($this->generateUuid(), title: 'いのライブ');
        $this->getInstance()->save($first);
        $this->getInstance()->save($second);
        $this->getInstance()->save($this->createEvent($this->generateUuid(), title: '配信'));

        $criteria = new EventSearchCriteria(new Some('ライブ'), new None(), new None(), new None(), Sort::Title, Order::Desc, 1, PerPage::TwentyFive);

        $this->assertEquals([$second, $first], $this->getInstance()->search($criteria));
    }

    private function getInstance(): EventRepository
    {
        return $this->app->make(EventRepository::class);
    }
}
