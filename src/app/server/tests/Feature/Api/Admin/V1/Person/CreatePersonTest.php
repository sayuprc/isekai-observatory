<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Person;

use Illuminate\Testing\Fluent\AssertableJson;
use Person\Infrastructures\PersonRepository;
use Person\Route\PersonRouteMap;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class CreatePersonTest extends DatabaseTestCase
{
    use EntityFactory;
    use WithAuth;

    #[Test]
    public function canCreate(): void
    {
        $this->withAuth()
            ->postJson(route(PersonRouteMap::Create), [
                'name' => 'テスト人物',
            ])->assertStatus(200)
            ->assertJson(
                static fn (AssertableJson $json) => $json
                    ->has(
                        'person',
                        static fn (AssertableJson $json) => $json
                            ->whereType('personId', 'string')
                            ->where('name', 'テスト人物')
                            ->whereType('orderNo', 'integer'),
                    ),
            );
    }

    #[Test]
    public function createFailsWhenNameAlreadyExists(): void
    {
        $this->app->make(PersonRepository::class)->save(
            $this->createPerson($this->generateUuid(), 'テスト人物', 10),
        );

        $this->withAuth()
            ->postJson(route(PersonRouteMap::Create), [
                'name' => 'テスト人物',
            ])->assertStatus(400)
            ->assertExactJson([
                'code' => 'business_rule_violation',
                'message' => 'すでに使われている名前です "テスト人物"',
            ]);
    }

    #[Test]
    public function emptyParameters(): void
    {
        $this->withAuth()
            ->postJson(route(PersonRouteMap::Create), [
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
