<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Song;

use Illuminate\Testing\Fluent\AssertableJson;
use Media\Domain\Models\MediaType;
use Media\Infrastructures\MediaRepository;
use Person\Infrastructures\PersonRepository;
use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\SongType;
use Song\Infrastructures\Tag\SongTagRepository;
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
        $tagRepo = $this->app->make(SongTagRepository::class);
        $tagRepo->save($tag1 = $this->createSongTag($this->generateUuid(), 'タグA', 10));
        $tagRepo->save($tag2 = $this->createSongTag($this->generateUuid(), 'タグB', 20));
        $mediaRepo = $this->app->make(MediaRepository::class);
        $mediaRepo->save($media = $this->createMedia($this->generateUuid(), 'テストメディアMV', 'https://example.com/media', MediaType::Mv, true));

        $this->withAuth()
            ->postJson(route(SongRouteMap::Create), [
                'title' => 'テスト楽曲',
                'description' => 'テスト楽曲説明',
                'lyricsLink' => 'https://example.com/lyrics',
                'typeValue' => SongType::Original->value,
                'isDisplay' => true,
                'persons' => [
                    ['personId' => $person1->personId->value, 'role' => 1, 'orderNo' => 1],
                    ['personId' => $person2->personId->value, 'role' => 2, 'orderNo' => 2],
                    ['personId' => $person3->personId->value, 'role' => 3, 'orderNo' => 3],
                ],
                'tags' => [
                    ['songTagId' => $tag2->songTagId->value],
                    ['songTagId' => $tag1->songTagId->value],
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
                            ->where('lyricsLink', 'https://example.com/lyrics')
                            ->where('type', [
                                'name' => SongType::Original->getName(),
                                'value' => SongType::Original->value,
                            ])
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
                            ->where('tags', [[
                                'songTagId' => $tag1->songTagId->value,
                                'name' => $tag1->name->value,
                            ], [
                                'songTagId' => $tag2->songTagId->value,
                                'name' => $tag2->name->value,
                            ]])
                            ->where('media', [[
                                'mediaId' => $media->mediaId->value,
                                'title' => $media->title->value,
                                'url' => $media->url->value,
                                'publishedAt' => $media->publishedAt->value->format('Y-m-d\TH:i:sP'),
                                'type' => [
                                    'name' => $media->type->getName(),
                                    'value' => $media->type->value,
                                ],
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
                'lyricsLink' => null,
                'typeValue' => SongType::Original->value,
                'isDisplay' => true,
                'persons' => [],
                'tags' => [],
                'media' => [],
            ])->assertStatus(200)
            ->assertJsonPath('song.lyricsLink', null)
            ->assertJsonPath('song.media', []);
    }

    #[Test]
    public function createFailsWithNotExistsSongTag(): void
    {
        $this->withAuth()
            ->postJson(route(SongRouteMap::Create), [
                'title' => 'テスト楽曲',
                'description' => 'テスト楽曲説明',
                'lyricsLink' => null,
                'typeValue' => SongType::Original->value,
                'isDisplay' => true,
                'persons' => [],
                'tags' => [['songTagId' => $this->generateUuid()]],
                'media' => [],
            ])->assertStatus(400);
    }

    #[Test]
    public function createFailsWithDuplicateSongTag(): void
    {
        $tagRepo = $this->app->make(SongTagRepository::class);
        $tagRepo->save($tag = $this->createSongTag($this->generateUuid(), 'タグA', 10));

        $this->withAuth()
            ->postJson(route(SongRouteMap::Create), [
                'title' => 'テスト楽曲',
                'description' => 'テスト楽曲説明',
                'lyricsLink' => null,
                'typeValue' => SongType::Original->value,
                'isDisplay' => true,
                'persons' => [],
                'tags' => [
                    ['songTagId' => $tag->songTagId->value],
                    ['songTagId' => $tag->songTagId->value],
                ],
                'media' => [],
            ])->assertStatus(400)
            ->assertJson(
                static fn (AssertableJson $json) => $json
                    ->where('code', 'business_rule_violation')
                    ->where('message', '同じ楽曲タグを複数指定することはできません。'),
            );
    }

    #[Test]
    public function emptyParameters(): void
    {
        $this->markTestSkipped('実装する');
    }
}
