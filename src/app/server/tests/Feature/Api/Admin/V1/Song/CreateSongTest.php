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
use Tag\Infrastructures\TagRepository;
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
        $tagRepo = $this->app->make(TagRepository::class);
        $tagRepo->save($tag1 = $this->createTag($this->generateUuid(), 'タグA', 10));
        $tagRepo->save($tag2 = $this->createTag($this->generateUuid(), 'タグB', 20));
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
                'tags' => [
                    ['tagId' => $tag2->tagId->value],
                    ['tagId' => $tag1->tagId->value],
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
                            ->where('tags', [[
                                'tagId' => $tag1->tagId->value,
                                'name' => $tag1->name->value,
                            ], [
                                'tagId' => $tag2->tagId->value,
                                'name' => $tag2->name->value,
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
                'tags' => [],
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
                'tags' => [],
                'media' => [],
            ])->assertStatus(200)
            ->assertJsonPath('song.type', SongType::Guest->value);
    }

    #[Test]
    public function createFailsWithNotExistsTag(): void
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
                'tags' => [['tagId' => $this->generateUuid()]],
                'media' => [],
            ])->assertStatus(400);
    }

    #[Test]
    public function createFailsWithDuplicateTag(): void
    {
        $tagRepo = $this->app->make(TagRepository::class);
        $tagRepo->save($tag = $this->createTag($this->generateUuid(), 'タグA', 10));

        $this->withAuth()
            ->postJson(route(SongRouteMap::Create), [
                'title' => 'テスト楽曲',
                'description' => 'テスト楽曲説明',
                'note' => '',
                'lyricsLink' => null,
                'type' => SongType::Original->value,
                'isDisplay' => true,
                'persons' => [],
                'tags' => [
                    ['tagId' => $tag->tagId->value],
                    ['tagId' => $tag->tagId->value],
                ],
                'media' => [],
            ])->assertStatus(400)
            ->assertJson(
                static fn (AssertableJson $json) => $json
                    ->where('code', 'business_rule_violation')
                    ->where('message', '同じタグを複数指定することはできません。'),
            );
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
                'tags' => [],
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
