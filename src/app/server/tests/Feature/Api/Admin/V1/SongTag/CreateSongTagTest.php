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

class CreateSongTagTest extends DatabaseTestCase
{
    use EntityFactory;
    use WithAuth;

    #[Test]
    public function canCreate(): void
    {
        $this->withAuth()
            ->postJson(route(SongTagRouteMap::Create), [
                'name' => 'テストタグA',
            ])->assertStatus(200)
            ->assertJson(
                static fn (AssertableJson $json) => $json
                    ->has(
                        'tag',
                        static fn (AssertableJson $json) => $json
                            ->whereType('songTagId', 'string')
                            ->where('name', 'テストタグA')
                            ->where('orderNo', 10),
                    ),
            );
    }

    #[Test]
    public function createFailsWhenNameAlreadyExists(): void
    {
        $this->app->make(SongTagRepository::class)->save(
            $this->createSongTag($this->generateUuid(), 'テストタグA', 10),
        );

        $this->withAuth()
            ->postJson(route(SongTagRouteMap::Create), [
                'name' => 'テストタグA',
            ])->assertStatus(400)
            ->assertExactJson([
                'code' => 'business_rule_violation',
                'message' => 'すでに使われている名前です "テストタグA"',
            ]);
    }

    #[Test]
    public function emptyParameters(): void
    {
        $this->withAuth()
            ->postJson(route(SongTagRouteMap::Create), [
                'name' => '',
            ])->assertStatus(422)
            ->assertJson(
                static fn (AssertableJson $json) => $json
                    ->where('code', 'validation_failed')
                    ->whereType('message', 'string')
                    ->has(
                        'details',
                        1,
                        static fn (AssertableJson $json) => $json
                            ->where('field', 'name')
                            ->whereType('message', 'string'),
                    ),
            );
    }
}
