<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Venue;

use PHPUnit\Framework\Attributes\Test;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditTargetType;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\Concerns\AssertsAuditLog;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;
use Venue\Domain\Models\VenueKind;
use Venue\Route\VenueRouteMap;

class DeleteVenueTest extends DatabaseTestCase
{
    use AssertsAuditLog;
    use EntityFactory;
    use EntityStore;
    use WithAuth;

    #[Test]
    public function canDelete(): void
    {
        $venueId = $this->generateUuid();
        $this->storeVenues($this->createVenue($venueId, '会場サンプルA', VenueKind::Physical));
        $client = $this->withAuth();

        $client->delete(route(VenueRouteMap::Delete, $venueId))->assertStatus(204);
        $client->get(route(VenueRouteMap::Get, $venueId))->assertStatus(404);

        $this->assertAuditLogCount(1);
        $log = $this->findAuditLog(AuditAction::Delete, AuditTargetType::Venue, $venueId);
        $this->assertSame('会場サンプルA', $log['snapshot']['name']);
    }

    #[Test]
    public function notFound(): void
    {
        $this->withAuth()
            ->delete(route(VenueRouteMap::Delete, $this->generateUuid()))
            ->assertStatus(404);
    }
}
