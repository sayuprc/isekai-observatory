<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Person;

use Person\Route\PersonRouteMap;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class SearchPersonTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;
    use WithAuth;

    #[Test]
    public function searchAll(): void
    {
        $uuid = $this->generateUuid();

        $this->storePersons($this->createPerson($uuid, 'テスト人物', 1));

        $this->withAuth()
            ->get(route(PersonRouteMap::Search))
            ->assertStatus(200)
            ->assertExactJson([
                'persons' => [
                    [
                        'personId' => $uuid,
                        'name' => 'テスト人物',
                        'orderNo' => 1,
                    ],
                ],
                'maxPage' => 1,
            ]);
    }

    #[Test]
    public function searchByName(): void
    {
        $uuid1 = $this->generateUuid();
        $uuid2 = $this->generateUuid();

        $this->storePersons(
            $this->createPerson($uuid1, 'テスト人物1', 1),
            $this->createPerson($uuid2, 'テスト人物2', 2),
        );

        $this->withAuth()
            ->get(route(PersonRouteMap::Search, ['name' => 'テスト人物1']))
            ->assertStatus(200)
            ->assertExactJson([
                'persons' => [
                    [
                        'personId' => $uuid1,
                        'name' => 'テスト人物1',
                        'orderNo' => 1,
                    ],
                ],
                'maxPage' => 1,
            ]);
    }

    #[Test]
    public function searchSortByNameDesc(): void
    {
        $uuid1 = $this->generateUuid();
        $uuid2 = $this->generateUuid();

        $this->storePersons(
            $this->createPerson($uuid1, 'あ', 1),
            $this->createPerson($uuid2, 'い', 2),
        );

        $this->withAuth()
            ->get(route(PersonRouteMap::Search, ['sort' => 'name', 'order' => 'desc']))
            ->assertStatus(200)
            ->assertExactJson([
                'persons' => [
                    [
                        'personId' => $uuid2,
                        'name' => 'い',
                        'orderNo' => 2,
                    ],
                    [
                        'personId' => $uuid1,
                        'name' => 'あ',
                        'orderNo' => 1,
                    ],
                ],
                'maxPage' => 1,
            ]);
    }
}
