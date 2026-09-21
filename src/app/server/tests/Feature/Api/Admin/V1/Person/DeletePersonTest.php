<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Person;

use Person\Route\PersonRouteMap;
use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\SongType;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class DeletePersonTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;
    use WithAuth;

    #[Test]
    public function canDelete(): void
    {
        $uuid = $this->generateUuid();

        $this->storePersons($this->createPerson($uuid, '人物', 1));

        $this->withAuth()
            ->delete(route(PersonRouteMap::Delete, $uuid))
            ->assertStatus(204);
    }

    #[Test]
    public function invalidPersonId(): void
    {
        $this->withAuth()
            ->delete(route(PersonRouteMap::Delete, 'invalid-id'))
            ->assertStatus(422)
            ->assertExactJson([
                'code' => 'validation_failed',
                'message' => '入力内容に誤りがあります',
                'details' => [
                    [
                        'field' => '',
                        'message' => '予期せぬエラー',
                    ],
                ],
            ]);
    }

    #[Test]
    public function cannotDeleteWhenUsedInSong(): void
    {
        $personId = $this->generateUuid();

        $this->storePersons($this->createPerson($personId, '人物', 1));
        $this->storeSongs($this->createSong(
            $this->generateUuid(),
            '曲名',
            '説明',
            SongType::Original,
            true,
            1,
            [],
            [['personId' => $personId, 'role' => 1, 'orderNo' => 1]],
        ));

        $this->withAuth()
            ->delete(route(PersonRouteMap::Delete, $personId))
            ->assertStatus(400)
            ->assertExactJson([
                'code' => 'business_rule_violation',
                'message' => 'この人物は楽曲に使用されているため削除できません',
            ]);
    }
}
