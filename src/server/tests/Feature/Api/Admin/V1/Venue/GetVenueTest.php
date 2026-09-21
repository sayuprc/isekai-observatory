<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Venue;

use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;
use Venue\Domain\Models\VenueKind;
use Venue\Route\VenueRouteMap;

class GetVenueTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;
    use WithAuth;

    #[Test]
    public function found(): void
    {
        $venueId = $this->generateUuid();
        $this->storeVenues($this->createVenue($venueId, '会場サンプルA', VenueKind::Physical));

        $this->withAuth()
            ->get(route(VenueRouteMap::Get, $venueId))
            ->assertStatus(200)
            ->assertExactJson([
                'venue' => [
                    'venueId' => $venueId,
                    'name' => '会場サンプルA',
                    'kind' => ['name' => '現地', 'value' => 1],
                ],
            ]);
    }

    #[Test]
    public function notFound(): void
    {
        $this->withAuth()
            ->get(route(VenueRouteMap::Get, $this->generateUuid()))
            ->assertStatus(404);
    }
}
