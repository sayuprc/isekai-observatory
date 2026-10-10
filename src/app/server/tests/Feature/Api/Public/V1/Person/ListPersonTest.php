<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Public\V1\Person;

use Person\Route\PublicPersonRouteMap;
use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\Persons\SongPersonRole;
use Song\Domain\Models\SongType;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class ListPersonTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function returnsOnlyPersonsReferencedByPublicRecords(): void
    {
        $songId = $this->generateUuid();
        $privateSongId = $this->generateUuid();
        $creditedId = $this->generateUuid();
        $coVocalistId = $this->generateUuid();
        $groupMemberId = $this->generateUuid();
        $privateOnlyId = $this->generateUuid();
        $unusedId = $this->generateUuid();
        $personGroupId = $this->generateUuid();

        $this->storePersons(
            $this->createPerson($creditedId, '公開楽曲の作家', 1),
            $this->createPerson($coVocalistId, '公開イベントの共演者', 2),
            $this->createPerson($groupMemberId, '公開グループのメンバー', 3),
            $this->createPerson($privateOnlyId, '非公開記録だけの人物', 4),
            $this->createPerson($unusedId, '未使用の人物', 5),
        );
        $this->storePersonGroups($this->createPersonGroup($personGroupId, 'グループ', [$coVocalistId, $groupMemberId]));
        $this->storeSongs(
            $this->createSong($songId, '公開楽曲', '説明', SongType::Original, true, 1, [], [
                ['personId' => $creditedId, 'role' => SongPersonRole::Lyricist->value, 'orderNo' => 1],
            ]),
            $this->createSong($privateSongId, '非公開楽曲', '説明', SongType::Original, false, 2, [], [
                ['personId' => $privateOnlyId, 'role' => SongPersonRole::Composer->value, 'orderNo' => 1],
            ]),
        );
        $this->storeEvents(
            $this->createEvent($this->generateUuid(), performances: [['performanceId' => $this->generateUuid(), 'songId' => $songId, 'orderNo' => 1, 'coVocalists' => [
                ['personId' => $coVocalistId, 'creditName' => null, 'personGroupId' => $personGroupId, 'orderNo' => 1],
            ]]]),
            $this->createEvent($this->generateUuid(), isDisplay: false, performances: [['performanceId' => $this->generateUuid(), 'songId' => $songId, 'orderNo' => 1, 'coVocalists' => [
                ['personId' => $privateOnlyId, 'creditName' => null, 'personGroupId' => null, 'orderNo' => 1],
            ]]]),
        );

        $this->get(route(PublicPersonRouteMap::List))
            ->assertStatus(200)
            ->assertExactJson([
                'items' => [
                    ['personId' => $creditedId, 'name' => '公開楽曲の作家'],
                    ['personId' => $coVocalistId, 'name' => '公開イベントの共演者'],
                    ['personId' => $groupMemberId, 'name' => '公開グループのメンバー'],
                ],
            ]);
    }

    #[Test]
    public function paginatesInOrderNoWithPageToken(): void
    {
        $songId = $this->generateUuid();
        $firstId = $this->generateUuid();
        $secondId = $this->generateUuid();
        $this->storePersons(
            $this->createPerson($secondId, '作曲者', 2),
            $this->createPerson($firstId, '作詞者', 1),
        );
        $this->storeSongs($this->createSong($songId, '公開楽曲', '説明', SongType::Original, true, 1, [], [
            ['personId' => $secondId, 'role' => SongPersonRole::Composer->value, 'orderNo' => 1],
            ['personId' => $firstId, 'role' => SongPersonRole::Lyricist->value, 'orderNo' => 2],
        ]));

        $first = $this->get(route(PublicPersonRouteMap::List, ['pageSize' => 1]))
            ->assertStatus(200)
            ->assertJsonPath('items.0.personId', $firstId);

        $this->get(route(PublicPersonRouteMap::List, ['pageSize' => 1, 'pageToken' => $first->json('nextPageToken')]))
            ->assertStatus(200)
            ->assertJsonPath('items.0.personId', $secondId)
            ->assertJsonMissingPath('nextPageToken');
    }
}
