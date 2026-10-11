<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Person;

use Event\Infrastructures\EventRepository;
use Person\Route\PersonRouteMap;
use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\Persons\SongPersonRole;
use Song\Domain\Models\SongType;
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
                        'songCount' => 0,
                        'performanceCount' => 0,
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
                        'songCount' => 0,
                        'performanceCount' => 0,
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
                        'songCount' => 0,
                        'performanceCount' => 0,
                    ],
                    [
                        'personId' => $uuid1,
                        'name' => 'あ',
                        'orderNo' => 1,
                        'songCount' => 0,
                        'performanceCount' => 0,
                    ],
                ],
                'maxPage' => 1,
            ]);
    }

    #[Test]
    public function returnsSongAndPerformanceCounts(): void
    {
        $personId = $this->generateUuid();
        $songId = $this->generateUuid();
        $this->storePersons($this->createPerson($personId, '作詞作曲家', 1));
        // 1 曲で作詞と作曲を兼ねても、楽曲は 1 件と数える
        $this->storeSongs($this->createSong($songId, '楽曲', '説明', SongType::Original, true, 1, [], tagsOrPersons: [
            ['personId' => $personId, 'role' => SongPersonRole::Lyricist->value, 'orderNo' => 1],
            ['personId' => $personId, 'role' => SongPersonRole::Composer->value, 'orderNo' => 1],
        ]));
        $this->app->make(EventRepository::class)->save($this->createEvent($this->generateUuid(), performances: [
            ['performanceId' => $this->generateUuid(), 'songId' => $songId, 'orderNo' => 1, 'coVocalists' => [
                ['personId' => $personId, 'creditName' => null, 'personGroupId' => null, 'orderNo' => 1],
            ]],
        ]));

        $this->withAuth()
            ->getJson(route(PersonRouteMap::Search))
            ->assertStatus(200)
            ->assertJsonPath('persons.0.songCount', 1)
            ->assertJsonPath('persons.0.performanceCount', 1);
    }
}
