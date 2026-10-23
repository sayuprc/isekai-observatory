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
                'type' => ReleaseGroupType::Album->value,
                'description' => '1st アルバム',
                'note' => '備考',
                'isDisplay' => true,
                'orderNo' => 1,
                'tags' => [],
            ])->assertStatus(200)
            ->assertJson(
                static fn (AssertableJson $json) => $json
                    ->has(
                        'releaseGroup',
                        static fn (AssertableJson $json) => $json
                            ->whereType('releaseGroupId', 'string')
                            ->where('title', '観測された春')
                            ->where('type', ReleaseGroupType::Album->value)
                            ->where('description', '1st アルバム')
                            ->where('note', '備考')
                            ->where('isDisplay', true)
                            ->where('orderNo', 1)
                            ->where('tags', []),
                    ),
            );

        $this->assertDatabaseHas('release_groups', [
            'title' => '観測された春',
            'type' => ReleaseGroupType::Album->value,
            'description' => '1st アルバム',
            'note' => '備考',
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
                'type' => ReleaseGroupType::Album->value,
                'description' => '',
                'note' => '',
                'isDisplay' => true,
                'orderNo' => 7,
                'tags' => [],
            ])->assertStatus(200)
            ->assertJsonPath('releaseGroup.orderNo', 7);
    }

    #[Test]
    public function forbidden(): void
    {
        $this->withGeneralAuth()
            ->postJson(route(ReleaseGroupRouteMap::Create), [
                'title' => '観測された春',
                'type' => ReleaseGroupType::Album->value,
                'description' => '',
                'note' => '',
                'isDisplay' => true,
                'orderNo' => 1,
                'tags' => [],
            ])->assertStatus(403);
    }

    #[Test]
    public function canCreateWithTagsSortedByTagOrder(): void
    {
        $firstTagId = $this->generateUuid();
        $secondTagId = $this->generateUuid();
        $this->storeTags($this->createTag($secondTagId, 'タグB', 20), $this->createTag($firstTagId, 'タグA', 10));

        $this->withAuth()
            ->postJson(route(ReleaseGroupRouteMap::Create), [
                'title' => 'タグ付きの作品',
                'type' => ReleaseGroupType::Album->value,
                'description' => '',
                'note' => '',
                'isDisplay' => true,
                'orderNo' => 1,
                'tags' => [['tagId' => $secondTagId], ['tagId' => $firstTagId]],
            ])
            ->assertStatus(200)
            ->assertJsonPath('releaseGroup.tags', [
                ['tagId' => $firstTagId, 'name' => 'タグA'],
                ['tagId' => $secondTagId, 'name' => 'タグB'],
            ]);
    }

    #[Test]
    public function rejectsDuplicatedTag(): void
    {
        $tagId = $this->generateUuid();
        $this->storeTags($this->createTag($tagId, 'タグA', 10));

        $this->withAuth()
            ->postJson(route(ReleaseGroupRouteMap::Create), [
                'title' => 'タグが重複した作品',
                'type' => ReleaseGroupType::Album->value,
                'description' => '',
                'note' => '',
                'isDisplay' => true,
                'orderNo' => 1,
                'tags' => [['tagId' => $tagId], ['tagId' => $tagId]],
            ])
            ->assertStatus(400)
            ->assertJsonPath('code', 'business_rule_violation');
    }
}
