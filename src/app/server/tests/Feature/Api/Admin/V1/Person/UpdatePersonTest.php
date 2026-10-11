<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Person;

use Illuminate\Testing\Fluent\AssertableJson;
use Person\Route\PersonRouteMap;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class UpdatePersonTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;
    use WithAuth;

    #[Test]
    public function canUpdate(): void
    {
        $uuid = $this->generateUuid();

        $this->storePersons($this->createPerson($uuid, '人物', 10));

        $this->withAuth()
            ->putJson(route(PersonRouteMap::Update, $uuid), [
                'name' => 'テスト人物',
                'orderNo' => 20,
            ])->assertStatus(200)
            ->assertExactJson([
                'person' => [
                    'personId' => $uuid,
                    'name' => 'テスト人物',
                    'orderNo' => 20,
                ],
            ]);
    }

    #[Test]
    public function routePersonIdIsPrioritizedOverBodyPersonId(): void
    {
        $routePersonId = $this->generateUuid();
        $bodyPersonId = $this->generateUuid();

        $this->storePersons($this->createPerson($routePersonId, '人物', 10));

        $this->withAuth()
            ->putJson(route(PersonRouteMap::Update, $routePersonId), [
                'personId' => $bodyPersonId,
                'name' => 'テスト人物',
                'orderNo' => 20,
            ])->assertStatus(200)
            ->assertExactJson([
                'person' => [
                    'personId' => $routePersonId,
                    'name' => 'テスト人物',
                    'orderNo' => 20,
                ],
            ]);
    }

    #[Test]
    public function updateFailsWhenPersonDoesNotExist(): void
    {
        $personId = $this->generateUuid();

        $this->withAuth()
            ->putJson(route(PersonRouteMap::Update, $personId), [
                'name' => 'テスト人物',
                'orderNo' => 20,
            ])->assertStatus(404);
    }

    #[Test]
    public function updateFailsWhenNameAlreadyExists(): void
    {
        $targetId = $this->generateUuid();
        $otherId = $this->generateUuid();

        $this->storePersons(
            $this->createPerson($targetId, '人物', 10),
            $this->createPerson($otherId, 'テスト人物', 20),
        );

        $this->withAuth()
            ->putJson(route(PersonRouteMap::Update, $targetId), [
                'name' => 'テスト人物',
                'orderNo' => 30,
            ])->assertStatus(400)
            ->assertExactJson([
                'code' => 'business_rule_violation',
                'message' => 'すでに使われている名前です "テスト人物"',
            ]);
    }

    #[Test]
    public function emptyParameters(): void
    {
        $uuid = $this->generateUuid();

        $this->storePersons($this->createPerson($uuid, '人物', 10));

        $this->withAuth()
            ->putJson(route(PersonRouteMap::Update, $uuid), [
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
