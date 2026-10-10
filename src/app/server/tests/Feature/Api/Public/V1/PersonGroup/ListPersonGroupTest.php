<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Public\V1\PersonGroup;

use Person\Route\PublicPersonGroupRouteMap;
use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\SongType;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class ListPersonGroupTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function returnsOnlyGroupsOfCoVocalistsInPublicEvents(): void
    {
        $songId = $this->generateUuid();
        $firstMemberId = $this->generateUuid();
        $secondMemberId = $this->generateUuid();
        $publicGroupId = $this->generateUuid();
        $privateGroupId = $this->generateUuid();
        $unusedGroupId = $this->generateUuid();

        $this->storeSongs($this->createSong($songId, '楽曲', '説明', SongType::Original, true, 1));
        $this->storePersons(
            $this->createPerson($firstMemberId, 'メンバー A', 1),
            $this->createPerson($secondMemberId, 'メンバー B', 2),
        );
        $this->storePersonGroups(
            $this->createPersonGroup($publicGroupId, '公開イベントのグループ', [$secondMemberId, $firstMemberId]),
            $this->createPersonGroup($privateGroupId, '非公開イベントのグループ', [$firstMemberId]),
            $this->createPersonGroup($unusedGroupId, '未使用のグループ', [$firstMemberId]),
        );
        $this->storeEvents(
            $this->createEvent($this->generateUuid(), performances: [['performanceId' => $this->generateUuid(), 'songId' => $songId, 'orderNo' => 1, 'coVocalists' => [
                ['personId' => $secondMemberId, 'creditName' => null, 'personGroupId' => $publicGroupId, 'orderNo' => 1],
            ]]]),
            $this->createEvent($this->generateUuid(), isDisplay: false, performances: [['performanceId' => $this->generateUuid(), 'songId' => $songId, 'orderNo' => 1, 'coVocalists' => [
                ['personId' => $firstMemberId, 'creditName' => null, 'personGroupId' => $privateGroupId, 'orderNo' => 1],
            ]]]),
        );

        $this->get(route(PublicPersonGroupRouteMap::List))
            ->assertStatus(200)
            ->assertExactJson([
                'items' => [['personGroupId' => $publicGroupId, 'name' => '公開イベントのグループ', 'memberIds' => [$secondMemberId, $firstMemberId]]],
            ]);
    }
}
