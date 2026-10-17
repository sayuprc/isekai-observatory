<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Song;

use Illuminate\Testing\Fluent\AssertableJson;
use Media\Domain\Models\MediaType;
use Media\Infrastructures\MediaRepository;
use Person\Infrastructures\PersonRepository;
use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\SongType;
use Song\Route\SongRouteMap;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class CreateSongTest extends DatabaseTestCase
{
    use EntityFactory;
    use WithAuth;

    #[Test]
    public function canCreate(): void
    {
        $personRepo = $this->app->make(PersonRepository::class);
        $personRepo->save($person1 = $this->createPerson($this->generateUuid(), 'テスト作詞者', 1));
        $personRepo->save($person2 = $this->createPerson($this->generateUuid(), 'テスト作曲者', 1));
        $personRepo->save($person3 = $this->createPerson($this->generateUuid(), 'テスト編曲者', 1));
        $mediaRepo = $this->app->make(MediaRepository::class);
        $mediaRepo->save($media = $this->createMedia($this->generateUuid(), 'テストメディアMV', 'https://example.com/media', MediaType::Mv, true));

        $this->withAuth()
            ->postJson(route(SongRouteMap::Create), [
                'title' => 'テスト楽曲',
                'description' => 'テスト楽曲説明',
                'note' => '備考',
                'lyricsLink' => 'https://example.com/lyrics',
                'type' => SongType::Original->value,
                'isDisplay' => true,
                'persons' => [
                    ['personId' => $person1->personId->value, 'role' => 1, 'orderNo' => 1],
                    ['personId' => $person2->personId->value, 'role' => 2, 'orderNo' => 2],
                    ['personId' => $person3->personId->value, 'role' => 3, 'orderNo' => 3],
                ],
                'media' => [
                    ['mediaId' => $media->mediaId->value, 'orderNo' => 1],
                ],
            ])->assertStatus(200)
            ->assertJson(
                static fn (AssertableJson $json) => $json
                    ->has(
                        'song',
                        static fn (AssertableJson $json) => $json
                            ->whereType('songId', 'string')
                            ->where('title', 'テスト楽曲')
                            ->where('description', 'テスト楽曲説明')
                            ->where('note', '備考')
                            ->where('lyricsLink', 'https://example.com/lyrics')
                            ->where('type', SongType::Original->value)
                            ->where('isDisplay', true)
                            ->where('orderNo', 10)
                            ->where('persons', [[
                                'personId' => $person1->personId->value,
                                'name' => $person1->name->value,
                                'role' => 1,
                                'orderNo' => 1,
                            ], [
                                'personId' => $person2->personId->value,
                                'name' => $person2->name->value,
                                'role' => 2,
                                'orderNo' => 2,
                            ], [
                                'personId' => $person3->personId->value,
                                'name' => $person3->name->value,
                                'role' => 3,
                                'orderNo' => 3,
                            ]])
                            ->where('media', [[
                                'mediaId' => $media->mediaId->value,
                                'title' => $media->title->value,
                                'url' => $media->url->value,
                                'publishedAt' => $media->publishedAt->value->format('Y-m-d\TH:i:sP'),
                                'type' => $media->type->value,
                                'isDisplay' => true,
                                'orderNo' => 1,
                            ]]),
                    ),
            );
    }

    #[Test]
    public function canCreateWithNullLyricsLink(): void
    {
        $this->withAuth()
            ->postJson(route(SongRouteMap::Create), [
                'title' => 'テスト楽曲',
                'description' => 'テスト楽曲説明',
                'note' => '',
                'lyricsLink' => null,
                'type' => SongType::Original->value,
                'isDisplay' => true,
                'persons' => [],
                'media' => [],
            ])->assertStatus(200)
            ->assertJsonPath('song.lyricsLink', null)
            ->assertJsonPath('song.media', []);
    }

    #[Test]
    public function canCreateGuestSong(): void
    {
        $this->withAuth()
            ->postJson(route(SongRouteMap::Create), [
                'title' => 'テスト参加曲',
                'description' => 'テスト参加曲説明',
                'note' => '',
                'lyricsLink' => null,
                'type' => SongType::Guest->value,
                'isDisplay' => true,
                'persons' => [],
                'media' => [],
            ])->assertStatus(200)
            ->assertJsonPath('song.type', SongType::Guest->value);
    }

    #[Test]
    public function emptyParameters(): void
    {
        $this->withAuth()
            ->postJson(route(SongRouteMap::Create), [
                'title' => '',
                'description' => '',
                'note' => '',
                'lyricsLink' => null,
                'type' => 0,
                'isDisplay' => true,
                'persons' => [],
                'media' => [],
            ])->assertStatus(422)
            ->assertJson(
                static fn (AssertableJson $json) => $json
                    ->where('code', 'validation_failed')
                    ->whereType('message', 'string')
                    ->has('details', 2)
                    ->where('details.0.field', 'title')
                    ->whereType('details.0.message', 'string')
                    ->where('details.1.field', 'type')
                    ->whereType('details.1.message', 'string'),
            );
    }
}
