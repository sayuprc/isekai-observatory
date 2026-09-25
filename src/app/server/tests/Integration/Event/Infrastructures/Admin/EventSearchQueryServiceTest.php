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
use Support\Domain\SearchCriteria\Order;
use Support\Domain\SearchCriteria\PerPage;
use Support\Optional\None;
use Support\Optional\Some;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class EventSearchQueryServiceTest extends DatabaseTestCase
{
    use EntityFactory;

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
            [new EventSummary($eventId, 'テストライブ', EventType::Live, '2026-10-01', '2026-10-02', EventStatus::Normal, true)],
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
