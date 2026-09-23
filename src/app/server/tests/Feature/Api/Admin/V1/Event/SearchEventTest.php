<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Event;

use Event\Domain\Models\Event;
use Event\Route\EventRouteMap;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityStore;

class SearchEventTest extends DatabaseTestCase
{
    use EntityStore;
    use WithAuth;

    #[Test]
    public function canSearchByStatusAndDisplay(): void
    {
        $normalId = $this->generateUuid();
        $this->storeEvents(
            $this->createEvent($normalId, '通常のライブ', 1, true),
            $this->createEvent($this->generateUuid(), '中止のライブ', 3, true),
            $this->createEvent($this->generateUuid(), '非公開のライブ', 1, false),
        );

        $this->withAuth()
            ->getJson(route(EventRouteMap::Search, ['status' => '1', 'is_display' => 'true', 'per_page' => '25']))
            ->assertStatus(200)
            ->assertJsonCount(1, 'events')
            ->assertJsonPath('events.0.eventId', $normalId)
            ->assertJsonPath('maxPage', 1);
    }

    private function createEvent(string $eventId, string $title, int $statusValue, bool $isDisplay): Event
    {
        return Event::fromInput($eventId, [
            'title' => $title,
            'description' => '',
            'typeValue' => 1,
            'schedule' => ['startOn' => '2026-10-01', 'endOn' => null],
            'statusValue' => $statusValue,
            'isDisplay' => $isDisplay,
        ]);
    }
}
