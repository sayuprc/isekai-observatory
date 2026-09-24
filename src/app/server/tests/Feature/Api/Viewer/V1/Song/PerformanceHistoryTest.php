<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Viewer\V1\Song;

use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\SongType;
use Song\Route\ViewerSongRouteMap;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class PerformanceHistoryTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function listsPublicEventPerformanceOnSong(): void
    {
        $songId = $this->generateUuid();
        $personId = $this->generateUuid();
        $eventId = $this->generateUuid();
        $performanceId = $this->generateUuid();
        $this->storeSongs($this->createSong($songId, '披露曲', '説明', SongType::Original, true, 1));
        $this->storePersons($this->createPerson($personId, '共演者', 1));
        $this->storeEvents($this->createEvent(
            $eventId,
            title: '披露ライブ',
            performances: [['performanceId' => $performanceId, 'songId' => $songId, 'orderNo' => 1, 'coVocalists' => [['personId' => $personId, 'creditName' => null, 'orderNo' => 1]]]],
        ));

        $this->get(route(ViewerSongRouteMap::List, ['limit' => 1]))
            ->assertStatus(200)
            ->assertJsonPath('songs.0.songId', $songId)
            ->assertJsonPath('songs.0.performances.0.eventId', $eventId)
            ->assertJsonPath('songs.0.performances.0.eventTitle', '披露ライブ')
            ->assertJsonPath('songs.0.performances.0.coVocalistNames.0', '共演者');
    }

    #[Test]
    public function hidesPerformanceOfPrivateEvent(): void
    {
        $songId = $this->generateUuid();
        $this->storeSongs($this->createSong($songId, '披露曲', '説明', SongType::Original, true, 1));
        $this->storeEvents($this->createEvent(
            $this->generateUuid(),
            title: '非公開ライブ',
            isDisplay: false,
            performances: [['performanceId' => $this->generateUuid(), 'songId' => $songId, 'orderNo' => 1, 'coVocalists' => []]],
        ));

        $this->get(route(ViewerSongRouteMap::List, ['limit' => 1]))
            ->assertStatus(200)
            ->assertJsonPath('songs.0.songId', $songId)
            ->assertJsonCount(0, 'songs.0.performances');
    }
}
