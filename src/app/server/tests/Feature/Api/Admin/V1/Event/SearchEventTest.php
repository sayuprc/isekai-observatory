<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Event;

use Event\Domain\Models\EventStatus;
use Event\Route\EventRouteMap;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class SearchEventTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;
    use WithAuth;

    #[Test]
    public function canSearchByStatusAndDisplay(): void
    {
        $normalId = $this->generateUuid();
        $this->storeEvents(
            $this->createEvent($normalId, title: '通常のライブ'),
            $this->createEvent($this->generateUuid(), title: '中止のライブ', status: EventStatus::Cancelled),
            $this->createEvent($this->generateUuid(), title: '非公開のライブ', isDisplay: false),
        );

        $this->withAuth()
            ->getJson(route(EventRouteMap::Search, ['status' => '1', 'is_display' => 'true', 'per_page' => '25']))
            ->assertStatus(200)
            ->assertJsonCount(1, 'events')
            ->assertJsonPath('events.0.eventId', $normalId)
            ->assertJsonPath('maxPage', 1);
    }
}
