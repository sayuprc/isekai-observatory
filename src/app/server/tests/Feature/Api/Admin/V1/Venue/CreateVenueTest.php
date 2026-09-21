<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Venue;

use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\Test;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditTargetType;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\Concerns\AssertsAuditLog;
use Tests\Support\DatabaseTestCase;
use Venue\Route\VenueRouteMap;

class CreateVenueTest extends DatabaseTestCase
{
    use AssertsAuditLog;
    use WithAuth;

    #[Test]
    public function canCreateAndNormalizeName(): void
    {
        $response = $this->withAuth()
            ->postJson(route(VenueRouteMap::Create), [
                'name' => '  配信先サンプルB  ',
                'kind' => 2,
            ])
            ->assertStatus(200)
            ->assertJson(
                static fn (AssertableJson $json) => $json
                    ->has('venue', static fn (AssertableJson $json) => $json
                        ->whereType('venueId', 'string')
                        ->where('name', '配信先サンプルB')
                        ->where('kind.name', 'オンライン')
                        ->where('kind.value', 2)),
            );

        $venueId = $response->json('venue.venueId');
        $this->assertIsString($venueId);
        $this->assertAuditLogCount(1);
        $log = $this->findAuditLog(AuditAction::Create, AuditTargetType::Venue, $venueId);
        $this->assertSame('配信先サンプルB', $log['snapshot']['name']);
        $this->assertSame(2, $log['snapshot']['kind']);
    }

    #[Test]
    public function allowsDuplicateNameAndKind(): void
    {
        $payload = ['name' => '配信先サンプルB', 'kind' => 2];
        $client = $this->withAuth();

        $client->postJson(route(VenueRouteMap::Create), $payload)->assertStatus(200);
        $client->postJson(route(VenueRouteMap::Create), $payload)->assertStatus(200);
    }

    #[Test]
    public function rejectsTooLongName(): void
    {
        $client = $this->withAuth();

        $client
            ->postJson(route(VenueRouteMap::Create), ['name' => str_repeat('あ', 256), 'kind' => 1])
            ->assertStatus(422);
    }

    #[Test]
    public function generalUserWithoutPermissionIsForbidden(): void
    {
        $this->withGeneralAuth()
            ->postJson(route(VenueRouteMap::Create), ['name' => '会場サンプルA', 'kind' => 1])
            ->assertStatus(403);
    }
}
