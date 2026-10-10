<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Public\V1\Venue;

use PHPUnit\Framework\Attributes\Test;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;
use Venue\Domain\Models\VenueKind;
use Venue\Route\PublicVenueRouteMap;

class ListVenueTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function returnsOnlyVenuesReferencedByPublicEvents(): void
    {
        $publicVenueId = $this->generateUuid();
        $privateVenueId = $this->generateUuid();
        $unusedVenueId = $this->generateUuid();
        $this->storeVenues(
            $this->createVenue($publicVenueId, '公開イベントの会場', VenueKind::Physical),
            $this->createVenue($privateVenueId, '非公開イベントの会場', VenueKind::Physical),
            $this->createVenue($unusedVenueId, '未使用の会場', VenueKind::Online),
        );
        $this->storeEvents(
            $this->createEvent($this->generateUuid(), venues: [['venueId' => $publicVenueId, 'orderNo' => 1]]),
            $this->createEvent($this->generateUuid(), isDisplay: false, venues: [['venueId' => $privateVenueId, 'orderNo' => 1]]),
        );

        $this->get(route(PublicVenueRouteMap::List))
            ->assertStatus(200)
            ->assertExactJson([
                'items' => [['venueId' => $publicVenueId, 'name' => '公開イベントの会場', 'kind' => VenueKind::Physical->value]],
            ]);
    }

    #[Test]
    public function paginatesInNameOrderWithPageToken(): void
    {
        $firstId = $this->generateUuid();
        $secondId = $this->generateUuid();
        $this->storeVenues(
            $this->createVenue($secondId, 'B ホール', VenueKind::Physical),
            $this->createVenue($firstId, 'A ホール', VenueKind::Physical),
        );
        $this->storeEvents($this->createEvent(
            $this->generateUuid(),
            venues: [['venueId' => $secondId, 'orderNo' => 1], ['venueId' => $firstId, 'orderNo' => 2]],
        ));

        $first = $this->get(route(PublicVenueRouteMap::List, ['pageSize' => 1]))
            ->assertStatus(200)
            ->assertJsonPath('items.0.venueId', $firstId);

        $this->get(route(PublicVenueRouteMap::List, ['pageSize' => 1, 'pageToken' => $first->json('nextPageToken')]))
            ->assertStatus(200)
            ->assertJsonPath('items.0.venueId', $secondId)
            ->assertJsonMissingPath('nextPageToken');
    }
}
