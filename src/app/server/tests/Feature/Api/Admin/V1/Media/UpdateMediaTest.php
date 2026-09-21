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

class UpdateMediaTest extends DatabaseTestCase
{
    use EntityFactory;
    use WithAuth;

    #[Test]
    public function canUpdate(): void
    {
        $uuid = $this->generateUuid();

        $repository = $this->app->make(MediaRepository::class);
        $repository->save(
            $this->createMedia(
                $uuid,
                'テストメディアMV',
                'https://example.com/media',
                MediaType::Mv,
                true,
            ),
        );

        $this->withAuth()
            ->putJson(route(MediaRouteMap::Update, $uuid), [
                'title' => 'テストメディア配信アーカイブ',
                'url' => 'https://example.com/archive',
                'publishedAt' => '2024-04-02T10:20:30+09:00',
                'typeValue' => MediaType::LiveStream->value,
                'isDisplay' => false,
            ])->assertStatus(200)
            ->assertExactJson([
                'media' => [
                    'mediaId' => $uuid,
                    'title' => 'テストメディア配信アーカイブ',
                    'url' => 'https://example.com/archive',
                    'publishedAt' => '2024-04-02T10:20:30+09:00',
                    'type' => [
                        'name' => MediaType::LiveStream->getName(),
                        'value' => MediaType::LiveStream->value,
                    ],
                    'isDisplay' => false,
                ],
            ]);
    }

    #[Test]
    public function routeMediaIdIsPrioritizedOverBodyMediaId(): void
    {
        $routeMediaId = $this->generateUuid();
        $bodyMediaId = $this->generateUuid();

        $repository = $this->app->make(MediaRepository::class);
        $repository->save(
            $this->createMedia(
                $routeMediaId,
                'テストメディアMV',
                'https://example.com/media',
                MediaType::Mv,
                true,
            ),
        );

        $this->withAuth()
            ->putJson(route(MediaRouteMap::Update, $routeMediaId), [
                'mediaId' => $bodyMediaId,
                'title' => 'テストメディア配信アーカイブ',
                'url' => 'https://example.com/archive',
                'publishedAt' => '2024-04-02T10:20:30+09:00',
                'typeValue' => MediaType::Mv->value,
                'isDisplay' => true,
            ])->assertStatus(200)
            ->assertJsonPath('media.mediaId', $routeMediaId);
    }

    #[Test]
    public function updateFailsWhenMediaDoesNotExist(): void
    {
        $mediaId = $this->generateUuid();

        $this->withAuth()
            ->putJson(route(MediaRouteMap::Update, $mediaId), [
                'title' => 'テストメディア',
                'url' => 'https://example.com/media',
                'publishedAt' => '2024-04-02T10:20:30+09:00',
                'typeValue' => MediaType::Mv->value,
                'isDisplay' => true,
            ])->assertStatus(404);
    }

    #[Test]
    public function emptyParameters(): void
    {
        $uuid = $this->generateUuid();

        $repository = $this->app->make(MediaRepository::class);
        $repository->save(
            $this->createMedia(
                $uuid,
                'テストメディアMV',
                'https://example.com/media',
                MediaType::Mv,
                true,
            ),
        );

        $this->withAuth()
            ->putJson(route(MediaRouteMap::Update, $uuid), [
                'title' => '',
                'url' => '',
                'publishedAt' => '2024-04-02T10:20:30+09:00',
                'typeValue' => 0,
                'isDisplay' => true,
            ])->assertStatus(422)
            ->assertJson(
                static fn (AssertableJson $json) => $json
                    ->where('code', 'validation_failed')
                    ->whereType('message', 'string')
                    ->has('details', 3)
                    ->where('details.0.field', 'title')
                    ->whereType('details.0.message', 'string')
                    ->where('details.1.field', 'url')
                    ->whereType('details.1.message', 'string')
                    ->where('details.2.field', 'typeValue')
                    ->whereType('details.2.message', 'string'),
            );
    }

    #[Test]
    public function duplicateUrlCannotBeUpdated(): void
    {
        $targetMediaId = $this->generateUuid();
        $existingMediaId = $this->generateUuid();

        $repository = $this->app->make(MediaRepository::class);
        $repository->save(
            $this->createMedia(
                $targetMediaId,
                '更新対象メディア',
                'https://example.com/target',
                MediaType::Mv,
                true,
            ),
        );
        $repository->save(
            $this->createMedia(
                $existingMediaId,
                '既存メディア',
                'https://example.com/existing',
                MediaType::Mv,
                true,
            ),
        );

        $this->withAuth()
            ->putJson(route(MediaRouteMap::Update, $targetMediaId), [
                'title' => '更新対象メディア',
                'url' => 'https://example.com/existing',
                'publishedAt' => '2024-04-02T10:20:30+09:00',
                'typeValue' => MediaType::Mv->value,
                'isDisplay' => true,
            ])->assertStatus(400)
            ->assertJson(
                static fn (AssertableJson $json) => $json
                    ->where('code', 'business_rule_violation')
                    ->where('message', '同じURLのメディアが既に存在します'),
            );
    }
}
