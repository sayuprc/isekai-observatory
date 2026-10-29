<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Tag;

use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\Test;
use Tag\Infrastructures\TagRepository;
use Tag\Route\TagRouteMap;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class UpdateTagTest extends DatabaseTestCase
{
    use EntityFactory;
    use WithAuth;

    #[Test]
    public function canUpdate(): void
    {
        $uuid = $this->generateUuid();

        $this->app->make(TagRepository::class)->save($this->createTag($uuid, '旧タグ', 1));

        $this->withAuth()
            ->putJson(route(TagRouteMap::Update, $uuid), [
                'name' => 'テストタグA',
                'orderNo' => 2,
            ])
            ->assertStatus(200)
            ->assertExactJson([
                'tag' => [
                    'tagId' => $uuid,
                    'name' => 'テストタグA',
                    'orderNo' => 2,
                ],
            ]);
    }

    #[Test]
    public function routeTagIdIsPrioritizedOverBodyTagId(): void
    {
        $routeTagId = $this->generateUuid();
        $bodyTagId = $this->generateUuid();

        $this->app->make(TagRepository::class)->save($this->createTag($routeTagId, '旧タグ', 1));

        $this->withAuth()
            ->putJson(route(TagRouteMap::Update, $routeTagId), [
                'tagId' => $bodyTagId,
                'name' => 'テストタグA',
                'orderNo' => 2,
            ])
            ->assertStatus(200)
            ->assertExactJson([
                'tag' => [
                    'tagId' => $routeTagId,
                    'name' => 'テストタグA',
                    'orderNo' => 2,
                ],
            ]);
    }

    #[Test]
    public function updateFailsWhenNameAlreadyExists(): void
    {
        $targetId = $this->generateUuid();
        $otherId = $this->generateUuid();

        $repository = $this->app->make(TagRepository::class);
        $repository->save($this->createTag($targetId, '旧タグ', 1));
        $repository->save($this->createTag($otherId, 'テストタグA', 2));

        $this->withAuth()
            ->putJson(route(TagRouteMap::Update, $targetId), [
                'name' => 'テストタグA',
                'orderNo' => 3,
            ])
            ->assertStatus(400)
            ->assertExactJson([
                'code' => 'business_rule_violation',
                'message' => 'すでに使われている名前です "テストタグA"',
            ]);
    }

    #[Test]
    public function updateFailsWhenTagDoesNotExist(): void
    {
        $uuid = $this->generateUuid();

        $this->withAuth()->putJson(route(TagRouteMap::Update, $uuid), [
            'name' => 'テストタグA',
            'orderNo' => 2,
        ])->assertStatus(404);
    }

    #[Test]
    public function emptyParameters(): void
    {
        $uuid = $this->generateUuid();

        $this->app->make(TagRepository::class)->save($this->createTag($uuid, '旧タグ', 1));

        $this->withAuth()
            ->putJson(route(TagRouteMap::Update, $uuid), [
                'name' => '',
                'orderNo' => 0,
            ])
            ->assertStatus(422)
            ->assertJson(
                static fn (AssertableJson $json) => $json->where('code', 'validation_failed')
                    ->whereType('message', 'string')
                    ->has('details', 2)
                    ->where('details.0.field', 'name')
                    ->whereType('details.0.message', 'string')
                    ->where('details.1.field', 'orderNo')
                    ->whereType('details.1.message', 'string'),
            );
    }
}
