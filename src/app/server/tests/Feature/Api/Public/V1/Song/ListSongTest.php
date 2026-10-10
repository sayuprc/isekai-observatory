<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Public\V1\Song;

use Media\Domain\Models\MediaType;
use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\Persons\SongPersonRole;
use Song\Domain\Models\SongType;
use Song\Route\PublicSongRouteMap;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class ListSongTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function returnsCreditsAndPublicMediaAsIds(): void
    {
        $songId = $this->generateUuid();
        $personId = $this->generateUuid();
        $visibleMediaId = $this->generateUuid();
        $hiddenMediaId = $this->generateUuid();

        $this->storePersons($this->createPerson($personId, '作家', 1));
        $this->storeMedia(
            $this->createMedia($visibleMediaId, '公開 MV', 'https://example.com/public', MediaType::Mv, true),
            $this->createMedia($hiddenMediaId, '非公開 MV', 'https://example.com/private', MediaType::Mv, false),
        );
        $this->storeSongs($this->createSong(
            $songId,
            '公開楽曲',
            '説明',
            SongType::Cover,
            true,
            1,
            [],
            [
                ['personId' => $personId, 'role' => SongPersonRole::Lyricist->value, 'orderNo' => 1],
                ['personId' => $personId, 'role' => SongPersonRole::Composer->value, 'orderNo' => 2],
            ],
            media: [['mediaId' => $hiddenMediaId, 'orderNo' => 1], ['mediaId' => $visibleMediaId, 'orderNo' => 2]],
        ));

        $this->get(route(PublicSongRouteMap::List))
            ->assertStatus(200)
            ->assertExactJson([
                'items' => [[
                    'songId' => $songId,
                    'title' => '公開楽曲',
                    'description' => '説明',
                    'type' => SongType::Cover->value,
                    'credits' => [
                        ['personId' => $personId, 'role' => SongPersonRole::Lyricist->value],
                        ['personId' => $personId, 'role' => SongPersonRole::Composer->value],
                    ],
                    'mediaIds' => [$visibleMediaId],
                ]],
            ]);
    }

    #[Test]
    public function excludesPrivateSong(): void
    {
        $this->storeSongs($this->createSong($this->generateUuid(), '非公開楽曲', '説明', SongType::Original, false, 1));

        $this->get(route(PublicSongRouteMap::List))
            ->assertStatus(200)
            ->assertExactJson(['items' => []]);
    }

    #[Test]
    public function paginatesInDescendingOrderNoWithPageToken(): void
    {
        $firstId = $this->generateUuid();
        $secondId = $this->generateUuid();
        $thirdId = $this->generateUuid();
        $this->storeSongs(
            $this->createSong($thirdId, '楽曲 C', '説明', SongType::Original, true, 1),
            $this->createSong($firstId, '楽曲 A', '説明', SongType::Original, true, 3),
            $this->createSong($secondId, '楽曲 B', '説明', SongType::Original, true, 2),
        );

        $first = $this->get(route(PublicSongRouteMap::List, ['pageSize' => 2]))
            ->assertStatus(200)
            ->assertJsonPath('items.0.songId', $firstId)
            ->assertJsonPath('items.1.songId', $secondId);

        $this->get(route(PublicSongRouteMap::List, ['pageSize' => 2, 'pageToken' => $first->json('nextPageToken')]))
            ->assertStatus(200)
            ->assertJsonCount(1, 'items')
            ->assertJsonPath('items.0.songId', $thirdId)
            ->assertJsonMissingPath('nextPageToken');
    }

    #[Test]
    public function rejectsInvalidPageToken(): void
    {
        $this->get(route(PublicSongRouteMap::List, ['pageToken' => '***']))
            ->assertStatus(400)
            ->assertJsonPath('code', 'business_rule_violation');
    }
}
