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

class UpdateReleaseGroupTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;
    use WithAuth;

    #[Test]
    public function canUpdate(): void
    {
        $releaseGroupId = $this->generateUuid();

        $this->storeReleaseGroups(
            $this->createReleaseGroup($releaseGroupId, '旧タイトル', ReleaseGroupType::Album, true),
        );

        $this->withAuth()
            ->putJson(route(ReleaseGroupRouteMap::Update, $releaseGroupId), [
                'title' => '新タイトル',
                'typeValue' => ReleaseGroupType::Single->value,
                'description' => '更新後の説明',
                'isDisplay' => false,
                'orderNo' => 3,
            ])->assertStatus(200)
            ->assertJson(
                static fn (AssertableJson $json) => $json
                    ->has(
                        'releaseGroup',
                        static fn (AssertableJson $json) => $json
                            ->where('releaseGroupId', $releaseGroupId)
                            ->where('title', '新タイトル')
                            ->where('typeValue', ReleaseGroupType::Single->value)
                            ->where('description', '更新後の説明')
                            ->where('isDisplay', false)
                            ->where('orderNo', 3),
                    ),
            );

        $this->assertDatabaseHas('release_groups', [
            'title' => '新タイトル',
            'type' => ReleaseGroupType::Single->value,
            'is_display' => false,
            'order_no' => 3,
        ]);
    }

    #[Test]
    public function notFound(): void
    {
        $this->withAuth()
            ->putJson(route(ReleaseGroupRouteMap::Update, $this->generateUuid()), [
                'title' => '新タイトル',
                'typeValue' => ReleaseGroupType::Album->value,
                'description' => '説明',
                'isDisplay' => true,
                'orderNo' => 1,
            ])->assertStatus(404);
    }

    #[Test]
    public function forbidden(): void
    {
        $releaseGroupId = $this->generateUuid();

        $this->storeReleaseGroups(
            $this->createReleaseGroup($releaseGroupId, '旧タイトル', ReleaseGroupType::Album, true),
        );

        $this->withGeneralAuth()
            ->putJson(route(ReleaseGroupRouteMap::Update, $releaseGroupId), [
                'title' => '新タイトル',
                'typeValue' => ReleaseGroupType::Album->value,
                'description' => '説明',
                'isDisplay' => true,
                'orderNo' => 1,
            ])->assertStatus(403);
    }
}
