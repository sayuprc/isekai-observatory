<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\PersonGroup;

use Person\Route\PersonGroupRouteMap;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class PersonGroupApiTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;
    use WithAuth;

    #[Test]
    public function canCreateWithMembers(): void
    {
        $personId = $this->generateUuid();
        $this->storePersons($this->createPerson($personId, '人物', 1));

        $this->withAuth()
            ->postJson(route(PersonGroupRouteMap::Create), [
                'name' => 'グループ',
                'members' => [['personId' => $personId, 'orderNo' => 1]],
            ])->assertStatus(200)
            ->assertJsonPath('personGroup.name', 'グループ')
            ->assertJsonPath('personGroup.members.0.personId', $personId)
            ->assertJsonPath('personGroup.members.0.name', '人物')
            ->assertJsonPath('personGroup.members.0.orderNo', 1);
    }

    #[Test]
    public function createFailsWhenNameIsEmpty(): void
    {
        $this->withAuth()
            ->postJson(route(PersonGroupRouteMap::Create), ['name' => '', 'members' => []])
            ->assertStatus(422)
            ->assertJsonPath('code', 'validation_failed')
            ->assertJsonPath('details.0.field', 'name');
    }

    #[Test]
    public function searchReturnsMembersInNameOrder(): void
    {
        $personId = $this->generateUuid();
        $this->storePersons($this->createPerson($personId, '人物', 1));
        $this->storePersonGroups(
            $this->createPersonGroup($this->generateUuid(), 'Bグループ'),
            $this->createPersonGroup($this->generateUuid(), 'Aグループ', [$personId]),
            $this->createPersonGroup($this->generateUuid(), '別名'),
        );

        $this->withAuth()
            ->getJson(route(PersonGroupRouteMap::Search, ['name' => 'グループ']))
            ->assertStatus(200)
            ->assertJsonPath('maxPage', 1)
            ->assertJsonCount(2, 'personGroups')
            ->assertJsonPath('personGroups.0.name', 'Aグループ')
            ->assertJsonPath('personGroups.0.members.0.name', '人物')
            ->assertJsonPath('personGroups.1.name', 'Bグループ')
            ->assertJsonCount(0, 'personGroups.1.members');
    }

    #[Test]
    public function getReturnsNotFound(): void
    {
        $this->withAuth()
            ->getJson(route(PersonGroupRouteMap::Get, $this->generateUuid()))
            ->assertStatus(404);
    }

    #[Test]
    public function canUpdate(): void
    {
        $personGroupId = $this->generateUuid();
        $this->storePersonGroups($this->createPersonGroup($personGroupId, '旧グループ'));

        $this->withAuth()
            ->putJson(route(PersonGroupRouteMap::Update, $personGroupId), ['name' => '新グループ', 'members' => []])
            ->assertStatus(200)
            ->assertJsonPath('personGroup.personGroupId', $personGroupId)
            ->assertJsonPath('personGroup.name', '新グループ');
    }

    #[Test]
    public function canDelete(): void
    {
        $personGroupId = $this->generateUuid();
        $this->storePersonGroups($this->createPersonGroup($personGroupId, 'グループ'));

        $this->withAuth()
            ->delete(route(PersonGroupRouteMap::Delete, $personGroupId))
            ->assertStatus(204);
    }
}
