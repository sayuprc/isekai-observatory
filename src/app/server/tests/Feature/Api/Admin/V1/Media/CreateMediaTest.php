<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Media;

use Illuminate\Testing\Fluent\AssertableJson;
use Media\Domain\Models\MediaType;
use Media\Infrastructures\MediaRepository;
use Media\Route\MediaRouteMap;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class CreateMediaTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;
    use WithAuth;

    #[Test]
    public function canCreate(): void
    {
        $this->withAuth()
            ->postJson(route(MediaRouteMap::Create), [
                'title' => 'テストメディアMV',
                'url' => 'https://example.com/media',
                'publishedAt' => '2024-03-01T12:34:56+09:00',
                'type' => MediaType::Mv->value,
                'isDisplay' => true,
                'tags' => [],
            ])->assertStatus(200)
            ->assertJson(static fn (AssertableJson $json) => $json
                ->whereType('media.mediaId', 'string')
                ->where('media.title', 'テストメディアMV')
                ->where('media.url', 'https://example.com/media')
                ->where('media.publishedAt', '2024-03-01T12:34:56+09:00')
                ->where('media.type', MediaType::Mv->value)
                ->where('media.isDisplay', true)
                ->where('tags', []));
    }

    #[Test]
    public function duplicateUrlCannotBeCreated(): void
    {
        $repository = $this->app->make(MediaRepository::class);
        $repository->save(
            $this->createMedia(
                $this->generateUuid(),
                '既存メディア',
                'https://example.com/media',
                MediaType::Mv,
                true,
            ),
        );

        $this->withAuth()
            ->postJson(route(MediaRouteMap::Create), [
                'title' => '別タイトル',
                'url' => 'https://example.com/media',
                'publishedAt' => '2024-03-01T12:34:56+09:00',
                'type' => MediaType::Mv->value,
                'isDisplay' => true,
                'tags' => [],
            ])->assertStatus(400)
            ->assertJson(
                static fn (AssertableJson $json) => $json
                    ->where('code', 'business_rule_violation')
                    ->where('message', '同じURLのメディアが既に存在します'),
            );
    }

    #[Test]
    public function canCreateWithTagsSortedByTagOrder(): void
    {
        $firstTagId = $this->generateUuid();
        $secondTagId = $this->generateUuid();
        $this->storeTags($this->createTag($secondTagId, 'タグB', 20), $this->createTag($firstTagId, 'タグA', 10));

        $this->withAuth()
            ->postJson(route(MediaRouteMap::Create), [
                'title' => 'タグ付きのMV',
                'url' => 'https://example.com/tagged',
                'publishedAt' => '2024-03-01T12:34:56+09:00',
                'type' => MediaType::Mv->value,
                'isDisplay' => true,
                'tags' => [['tagId' => $secondTagId], ['tagId' => $firstTagId]],
            ])
            ->assertStatus(200)
            ->assertJsonPath('tags', [
                ['tagId' => $firstTagId, 'name' => 'タグA'],
                ['tagId' => $secondTagId, 'name' => 'タグB'],
            ]);
    }

    #[Test]
    public function rejectsDuplicatedTag(): void
    {
        $tagId = $this->generateUuid();
        $this->storeTags($this->createTag($tagId, 'タグA', 10));

        $this->withAuth()
            ->postJson(route(MediaRouteMap::Create), [
                'title' => 'タグが重複したMV',
                'url' => 'https://example.com/duplicated',
                'publishedAt' => '2024-03-01T12:34:56+09:00',
                'type' => MediaType::Mv->value,
                'isDisplay' => true,
                'tags' => [['tagId' => $tagId], ['tagId' => $tagId]],
            ])
            ->assertStatus(400)
            ->assertJsonPath('code', 'business_rule_violation');
    }
}
