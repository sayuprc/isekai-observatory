<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Venue;

use Event\Infrastructures\EventRepository;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;
use Venue\Domain\Models\VenueKind;
use Venue\Route\VenueRouteMap;

class SearchVenueTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;
    use WithAuth;

    #[Test]
    public function searchesByNameKindAndSortOrder(): void
    {
        $physicalId = $this->generateUuid();
        $onlineId = $this->generateUuid();
        $this->storeVenues(
            $this->createVenue($physicalId, '会場サンプルA', VenueKind::Physical),
            $this->createVenue($onlineId, '配信先サンプルB', VenueKind::Online),
        );

        $this
            ->withAuth()
            ->get(route(VenueRouteMap::Search, [
                'name' => '配信',
                'kind' => 2,
                'sort' => 'name',
                'order' => 'desc',
            ]))
            ->assertOk()
            ->assertExactJson([
                'venues' => [[
                    'venueId' => $onlineId,
                    'name' => '配信先サンプルB',
                    'kind' => 2,
                    'eventCount' => 0,
                ]],
                'maxPage' => 1,
            ]);
    }

    #[Test]
    public function generalUserWithoutPermissionIsForbidden(): void
    {
        $this->withGeneralAuth()->get(route(VenueRouteMap::Search))->assertForbidden();
    }

    #[Test]
    public function returnsEventCountUsingTheVenue(): void
    {
        $venueId = $this->generateUuid();
        $this->storeVenues($this->createVenue($venueId, '会場', VenueKind::Physical));
        $repository = $this->app->make(EventRepository::class);
        $repository->save($this->createEvent($this->generateUuid(), venues: [['venueId' => $venueId, 'orderNo' => 1]]));
        $repository->save($this->createEvent($this->generateUuid(), venues: [['venueId' => $venueId, 'orderNo' => 1]]));

        $this
            ->withAuth()
            ->get(route(VenueRouteMap::Search))
            ->assertOk()
            ->assertJsonPath('venues.0.eventCount', 2);
    }
}
