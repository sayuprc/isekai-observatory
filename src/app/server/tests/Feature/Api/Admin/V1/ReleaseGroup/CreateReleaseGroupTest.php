<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\ReleaseGroup;

use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\Test;
use Release\Domain\Models\ReleaseGroupType;
use Release\Route\ReleaseGroupRouteMap;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class CreateReleaseGroupTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;
    use WithAuth;

    #[Test]
    public function canCreate(): void
    {
        $this->withAuth()
            ->postJson(route(ReleaseGroupRouteMap::Create), [
                'title' => '観測された春',
                'typeValue' => ReleaseGroupType::Album->value,
                'description' => '1st アルバム',
                'isDisplay' => true,
                'orderNo' => 1,
            ])->assertStatus(200)
            ->assertJson(
                static fn (AssertableJson $json) => $json
                    ->has(
                        'releaseGroup',
                        static fn (AssertableJson $json) => $json
                            ->whereType('releaseGroupId', 'string')
                            ->where('title', '観測された春')
                            ->where('typeValue', ReleaseGroupType::Album->value)
                            ->where('description', '1st アルバム')
                            ->where('isDisplay', true)
                            ->where('orderNo', 1),
                    ),
            );

        $this->assertDatabaseHas('release_groups', [
            'title' => '観測された春',
            'type' => ReleaseGroupType::Album->value,
            'description' => '1st アルバム',
            'is_display' => true,
            'order_no' => 1,
        ]);
    }

    #[Test]
    public function usesSubmittedOrderNoOnCreate(): void
    {
        $this->storeReleaseGroups(
            $this->createReleaseGroup($this->generateUuid(), '既存の作品', ReleaseGroupType::Single, true, orderNo: 15),
        );

        $this->withAuth()
            ->postJson(route(ReleaseGroupRouteMap::Create), [
                'title' => '観測された春',
                'typeValue' => ReleaseGroupType::Album->value,
                'description' => '',
                'isDisplay' => true,
                'orderNo' => 7,
            ])->assertStatus(200)
            ->assertJsonPath('releaseGroup.orderNo', 7);
    }

    #[Test]
    public function forbidden(): void
    {
        $this->withGeneralAuth()
            ->postJson(route(ReleaseGroupRouteMap::Create), [
                'title' => '観測された春',
                'typeValue' => ReleaseGroupType::Album->value,
                'description' => '',
                'isDisplay' => true,
                'orderNo' => 1,
            ])->assertStatus(403);
    }
}
