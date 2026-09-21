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

class UpdateVenueTest extends DatabaseTestCase
{
    use AssertsAuditLog;
    use EntityFactory;
    use EntityStore;
    use WithAuth;

    #[Test]
    public function canUpdateNameAndKind(): void
    {
        $venueId = $this->generateUuid();
        $this->storeVenues($this->createVenue($venueId, '仮名', VenueKind::Physical));

        $this->withAuth()
            ->putJson(route(VenueRouteMap::Update, $venueId), ['name' => ' 配信先サンプルB ', 'kind' => 2])
            ->assertStatus(200)
            ->assertExactJson([
                'venue' => [
                    'venueId' => $venueId,
                    'name' => '配信先サンプルB',
                    'kind' => ['name' => 'オンライン', 'value' => 2],
                ],
            ]);

        $this->assertAuditLogCount(1);
        $log = $this->findAuditLog(AuditAction::Update, AuditTargetType::Venue, $venueId);
        $this->assertSame('配信先サンプルB', $log['snapshot']['name']);
        $this->assertSame(2, $log['snapshot']['kind']);
    }

    #[Test]
    public function notFound(): void
    {
        $this->withAuth()
            ->putJson(route(VenueRouteMap::Update, $this->generateUuid()), [
                'name' => '配信先サンプルB',
                'kind' => 2,
            ])
            ->assertStatus(404);
    }
}
