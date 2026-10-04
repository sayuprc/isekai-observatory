<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\PersonGroup;

use Event\Infrastructures\EventRepository;
use Person\Route\PersonGroupRouteMap;
use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\SongType;
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

    #[Test]
    public function searchReturnsPerformanceCountCreditedToTheGroup(): void
    {
        $memberId1 = $this->generateUuid();
        $memberId2 = $this->generateUuid();
        $personGroupId = $this->generateUuid();
        $songId = $this->generateUuid();
        $this->storePersons($this->createPerson($memberId1, 'メンバー1', 1), $this->createPerson($memberId2, 'メンバー2', 2));
        $this->storePersonGroups($this->createPersonGroup($personGroupId, 'グループ', [$memberId1, $memberId2]));
        $this->storeSongs($this->createSong($songId, '楽曲', '説明', SongType::Original, true, 1));
        // メンバー 2 人を同じ楽曲披露に入れても、楽曲披露は 1 件と数える
        $this->app->make(EventRepository::class)->save($this->createEvent($this->generateUuid(), performances: [
            ['performanceId' => $this->generateUuid(), 'songId' => $songId, 'orderNo' => 1, 'coVocalists' => [
                ['personId' => $memberId1, 'creditName' => null, 'personGroupId' => $personGroupId, 'orderNo' => 1],
                ['personId' => $memberId2, 'creditName' => null, 'personGroupId' => $personGroupId, 'orderNo' => 2],
            ]],
        ]));

        $this->withAuth()
            ->getJson(route(PersonGroupRouteMap::Search))
            ->assertStatus(200)
            ->assertJsonPath('personGroups.0.personGroupId', $personGroupId)
            ->assertJsonPath('personGroups.0.performanceCount', 1);
    }
}
