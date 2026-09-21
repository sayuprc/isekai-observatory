<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\SongTag;

use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\Test;
use Song\Infrastructures\Tag\SongTagRepository;
use Song\Route\Tag\SongTagRouteMap;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class UpdateSongTagTest extends DatabaseTestCase
{
    use EntityFactory;
    use WithAuth;

    #[Test]
    public function canUpdate(): void
    {
        $uuid = $this->generateUuid();

        $this->app->make(SongTagRepository::class)->save($this->createSongTag($uuid, '旧タグ', 1));

        $this->withAuth()
            ->putJson(route(SongTagRouteMap::Update, $uuid), [
                'name' => 'テストタグA',
                'orderNo' => 2,
            ])->assertStatus(200)
            ->assertExactJson([
                'tag' => [
                    'songTagId' => $uuid,
                    'name' => 'テストタグA',
                    'orderNo' => 2,
                ],
            ]);
    }

    #[Test]
    public function routeSongTagIdIsPrioritizedOverBodySongTagId(): void
    {
        $routeSongTagId = $this->generateUuid();
        $bodySongTagId = $this->generateUuid();

        $this->app->make(SongTagRepository::class)->save($this->createSongTag($routeSongTagId, '旧タグ', 1));

        $this->withAuth()
            ->putJson(route(SongTagRouteMap::Update, $routeSongTagId), [
                'songTagId' => $bodySongTagId,
                'name' => 'テストタグA',
                'orderNo' => 2,
            ])->assertStatus(200)
            ->assertExactJson([
                'tag' => [
                    'songTagId' => $routeSongTagId,
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

        $repository = $this->app->make(SongTagRepository::class);
        $repository->save($this->createSongTag($targetId, '旧タグ', 1));
        $repository->save($this->createSongTag($otherId, 'テストタグA', 2));

        $this->withAuth()
            ->putJson(route(SongTagRouteMap::Update, $targetId), [
                'name' => 'テストタグA',
                'orderNo' => 3,
            ])->assertStatus(400)
            ->assertExactJson([
                'code' => 'business_rule_violation',
                'message' => 'すでに使われている名前です "テストタグA"',
            ]);
    }

    #[Test]
    public function updateFailsWhenSongTagDoesNotExist(): void
    {
        $uuid = $this->generateUuid();

        $this->withAuth()
            ->putJson(route(SongTagRouteMap::Update, $uuid), [
                'name' => 'テストタグA',
                'orderNo' => 2,
            ])->assertStatus(404);
    }

    #[Test]
    public function emptyParameters(): void
    {
        $uuid = $this->generateUuid();

        $this->app->make(SongTagRepository::class)->save($this->createSongTag($uuid, '旧タグ', 1));

        $this->withAuth()
            ->putJson(route(SongTagRouteMap::Update, $uuid), [
                'name' => '',
                'orderNo' => 0,
            ])->assertStatus(422)
            ->assertJson(
                static fn (AssertableJson $json) => $json
                    ->where('code', 'validation_failed')
                    ->whereType('message', 'string')
                    ->has('details', 2)
                    ->where('details.0.field', 'name')
                    ->whereType('details.0.message', 'string')
                    ->where('details.1.field', 'orderNo')
                    ->whereType('details.1.message', 'string'),
            );
    }
}
