<?php

declare(strict_types=1);

namespace Tests\Integration\Event\Infrastructures;

use Event\Domain\Criteria\EventSearchCriteria;
use Event\Domain\Models\Event;
use Event\Domain\Models\EventStatus;
use Event\Domain\Models\EventType;
use Event\Infrastructures\EventRepository;
use Media\Domain\Models\MediaType;
use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\SongType;
use Support\Domain\SearchCriteria\Order;
use Support\Domain\SearchCriteria\PerPage;
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
        $this->storeSongs($this->createSong($songId, '披露曲', '説明', SongType::Original, false, 1));
        $this->storePersons($this->createPerson($personId, '共演者', 1));
        $this->storeVenues($this->createVenue($venueId, '会場', VenueKind::Physical));
        $this->storeMedia($this->createMedia($mediaId, '配信アーカイブ', 'https://example.com/archive', MediaType::LiveStream, true));

        $eventId = $this->generateUuid();
        $performanceId = $this->generateUuid();
        $setlistItemId = $this->generateUuid();
        $this->getInstance()->save($this->createEvent($eventId, [
            'venueIds' => [$venueId],
            'mediaIds' => [$mediaId],
            'sources' => [['displayName' => '公式', 'url' => 'https://example.com/live', 'orderNo' => 1]],
            'performances' => [['performanceId' => $performanceId, 'songId' => $songId, 'songTitle' => '披露曲', 'orderNo' => 1, 'coVocalists' => [['personId' => $personId, 'name' => '共演者', 'creditName' => 'ゲスト', 'orderNo' => 1]]]],
            'setlist' => [['setlistItemId' => $setlistItemId, 'orderNo' => 1, 'label' => '本編', 'performances' => [['performanceId' => $performanceId]]]],
        ]));

        $found = $this->getInstance()->find($eventId);

        $this->assertNotNull($found);
        $this->assertSame('テストライブ', $found->title);
        $this->assertSame(EventStatus::Normal, $found->status);
        $this->assertSame(['startOn' => '2026-10-01', 'endOn' => null], $found->schedule());
        $this->assertSame([['venue_id' => $venueId, 'name' => '会場', 'kind' => VenueKind::Physical->value, 'order_no' => 1]], $found->venues);
        $this->assertSame($mediaId, $found->media[0]['media_id']);
        $this->assertSame([['name' => '公式', 'url' => 'https://example.com/live', 'order_no' => 1]], $found->sources);
        $this->assertSame($performanceId, $found->performances[0]['performance_id']);
        $this->assertSame('披露曲', $found->performances[0]['song_title']);
        $this->assertFalse($found->performances[0]['song_is_display']);
        $this->assertSame([['person_id' => $personId, 'name' => '共演者', 'credit_name' => 'ゲスト', 'order_no' => 1]], $found->performances[0]['co_vocalists']);
        $this->assertSame($setlistItemId, $found->setlist[0]['setlist_item_id']);
        $this->assertSame($performanceId, $found->setlist[0]['performances'][0]['performance_id']);
    }

    #[Test]
    public function saveReplacesChildren(): void
    {
        $songId = $this->generateUuid();
        $this->storeSongs($this->createSong($songId, '披露曲', '説明', SongType::Original, true, 1));

        $eventId = $this->generateUuid();
        $this->getInstance()->save($this->createEvent($eventId, [
            'performances' => [['performanceId' => $this->generateUuid(), 'songId' => $songId, 'songTitle' => '披露曲', 'orderNo' => 1, 'coVocalists' => []]],
        ]));
        $this->getInstance()->save($this->createEvent($eventId, [
            'title' => '中止されたライブ',
            'statusValue' => EventStatus::Cancelled->value,
        ]));

        $found = $this->getInstance()->find($eventId);

        $this->assertNotNull($found);
        $this->assertSame('中止されたライブ', $found->title);
        $this->assertSame(EventStatus::Cancelled, $found->status);
        $this->assertSame([], $found->performances);
        $this->assertDatabaseCount('events', 1);
        $this->assertDatabaseCount('song_performances', 0);
    }

    #[Test]
    public function findNotFound(): void
    {
        $this->assertNull($this->getInstance()->find($this->generateUuid()));
    }

    #[Test]
    public function deletingRemovesOwnedChildren(): void
    {
        $songId = $this->generateUuid();
        $this->storeSongs($this->createSong($songId, '披露曲', '説明', SongType::Original, true, 1));

        $eventId = $this->generateUuid();
        $performanceId = $this->generateUuid();
        $this->getInstance()->save($this->createEvent($eventId, [
            'sources' => [['displayName' => '公式', 'url' => 'https://example.com/live', 'orderNo' => 1]],
            'performances' => [['performanceId' => $performanceId, 'songId' => $songId, 'songTitle' => '披露曲', 'orderNo' => 1, 'coVocalists' => []]],
            'setlist' => [['setlistItemId' => $this->generateUuid(), 'orderNo' => 1, 'label' => null, 'performances' => [['performanceId' => $performanceId]]]],
        ]));

        $this->getInstance()->delete($eventId);

        $this->assertNull($this->getInstance()->find($eventId));
        $this->assertDatabaseCount('event_sources', 0);
        $this->assertDatabaseCount('song_performances', 0);
        $this->assertDatabaseCount('event_setlist_items', 0);
    }

    #[Test]
    public function searchFiltersByStatusAndDisplay(): void
    {
        $normalId = $this->generateUuid();
        $this->getInstance()->save($this->createEvent($normalId));
        $this->getInstance()->save($this->createEvent($this->generateUuid(), ['statusValue' => EventStatus::Cancelled->value]));
        $this->getInstance()->save($this->createEvent($this->generateUuid(), ['isDisplay' => false]));

        $criteria = new EventSearchCriteria(null, EventType::Live->value, EventStatus::Normal->value, true, 'schedule', Order::Asc, 1, PerPage::TwentyFive);
        $events = $this->getInstance()->search($criteria);

        $this->assertCount(1, $events);
        $this->assertSame($normalId, $events[0]->eventId);
        $this->assertSame(1, $this->getInstance()->maxPage($criteria));
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function createEvent(string $eventId, array $overrides = []): Event
    {
        return Event::fromInput($eventId, [
            'title' => 'テストライブ',
            'description' => '',
            'typeValue' => EventType::Live->value,
            'schedule' => ['startOn' => '2026-10-01', 'endOn' => null],
            'statusValue' => EventStatus::Normal->value,
            'isDisplay' => true,
            'venueIds' => [],
            'mediaIds' => [],
            'sources' => [],
            'performances' => [],
            'setlist' => [],
            ...$overrides,
        ]);
    }

    private function getInstance(): EventRepository
    {
        return $this->app->make(EventRepository::class);
    }
}
