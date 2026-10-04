<?php

declare(strict_types=1);

namespace Tests\Integration\Event\Infrastructures\Admin;

use Event\Application\Admin\Query\EventSummary;
use Event\Domain\Criteria\EventSearchCriteria;
use Event\Domain\Criteria\Sort;
use Event\Domain\Models\Event;
use Event\Domain\Models\EventStatus;
use Event\Domain\Models\EventType;
use Event\Infrastructures\Admin\EventSearchQueryService;
use Event\Infrastructures\EventRepository;
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

class EventSearchQueryServiceTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function searchFiltersByStatusAndDisplay(): void
    {
        $eventId = $this->generateUuid();
        $this->saveEvents(
            $this->createEvent($eventId, startOn: '2026-10-01', endOn: '2026-10-02'),
            $this->createEvent($this->generateUuid(), status: EventStatus::Cancelled),
            $this->createEvent($this->generateUuid(), isDisplay: false),
        );

        $criteria = new EventSearchCriteria(new None(), new Some(EventType::Live), new Some(EventStatus::Normal), new Some(true));

        $this->assertEquals(
            [new EventSummary($eventId, 'テストライブ', EventType::Live, '2026-10-01', '2026-10-02', EventStatus::Normal, true, [], 0, 0, 0)],
            $this->getInstance()->search($criteria),
        );
        $this->assertSame(1, $this->getInstance()->maxPage($criteria));
    }

    #[Test]
    public function searchByTitleSortedByTitleDesc(): void
    {
        $firstId = $this->generateUuid();
        $secondId = $this->generateUuid();
        $this->saveEvents(
            $this->createEvent($firstId, title: 'あのライブ'),
            $this->createEvent($secondId, title: 'いのライブ'),
            $this->createEvent($this->generateUuid(), title: '配信'),
        );

        $criteria = new EventSearchCriteria(new Some('ライブ'), new None(), new None(), new None(), Sort::Title, Order::Desc, 1, PerPage::TwentyFive);

        $this->assertSame(
            [$secondId, $firstId],
            array_map(static fn (EventSummary $summary): string => $summary->eventId, $this->getInstance()->search($criteria)),
        );
    }

    #[Test]
    public function searchCountsChildrenAndListsVenueNamesInOrder(): void
    {
        $songId = $this->generateUuid();
        $firstVenueId = $this->generateUuid();
        $secondVenueId = $this->generateUuid();
        $this->storeSongs($this->createSong($songId, '披露曲', '説明', SongType::Original, true, 1));
        $this->storeVenues(
            $this->createVenue($firstVenueId, '会場', VenueKind::Physical),
            $this->createVenue($secondVenueId, '配信', VenueKind::Online),
        );

        $eventId = $this->generateUuid();
        $emptyEventId = $this->generateUuid();
        $firstPerformanceId = $this->generateUuid();
        $secondPerformanceId = $this->generateUuid();
        $this->saveEvents(
            $this->createEvent(
                $eventId,
                startOn: '2026-10-01',
                venues: [['venueId' => $secondVenueId, 'orderNo' => 1], ['venueId' => $firstVenueId, 'orderNo' => 2]],
                sources: [['displayName' => '公式', 'url' => 'https://example.com/live', 'orderNo' => 1]],
                performances: [
                    ['performanceId' => $firstPerformanceId, 'songId' => $songId, 'orderNo' => 1, 'coVocalists' => []],
                    ['performanceId' => $secondPerformanceId, 'songId' => $songId, 'orderNo' => 2, 'coVocalists' => []],
                ],
                setlist: [
                    ['setlistItemId' => $this->generateUuid(), 'orderNo' => 1, 'label' => 'MC', 'performanceIds' => []],
                    ['setlistItemId' => $this->generateUuid(), 'orderNo' => 2, 'label' => null, 'performanceIds' => [$firstPerformanceId]],
                    ['setlistItemId' => $this->generateUuid(), 'orderNo' => 3, 'label' => null, 'performanceIds' => [$secondPerformanceId]],
                ],
            ),
            $this->createEvent($emptyEventId, startOn: '2026-10-02'),
        );

        $this->assertEquals(
            [
                new EventSummary($eventId, 'テストライブ', EventType::Live, '2026-10-01', null, EventStatus::Normal, true, ['配信', '会場'], 2, 3, 1),
                new EventSummary($emptyEventId, 'テストライブ', EventType::Live, '2026-10-02', null, EventStatus::Normal, true, [], 0, 0, 0),
            ],
            $this->getInstance()->search(new EventSearchCriteria(new None(), new None(), new None(), new None())),
        );
    }

    private function saveEvents(Event ...$events): void
    {
        $repository = $this->app->make(EventRepository::class);
        foreach ($events as $event) {
            $repository->save($event);
        }
    }

    private function getInstance(): EventSearchQueryService
    {
        return $this->app->make(EventSearchQueryService::class);
    }
}
