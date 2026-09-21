<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Media;

use DateTimeImmutable;
use Media\Domain\Models\MediaType;
use Media\Infrastructures\MediaRepository;
use Media\Route\MediaRouteMap;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class SearchMediaTest extends DatabaseTestCase
{
    use EntityFactory;
    use WithAuth;

    #[Test]
    public function canSearchByTitle(): void
    {
        $repository = $this->app->make(MediaRepository::class);
        $repository->save($this->createMedia($this->generateUuid(), 'テストメディアMV', 'https://example.com/mv', MediaType::Mv, true, new DateTimeImmutable('2024-03-01 12:00:00')));
        $repository->save($this->createMedia($this->generateUuid(), '別の動画', 'https://example.com/other', MediaType::AudioVideo, true));

        $this->withAuth()
            ->getJson(route(MediaRouteMap::Search, ['title' => 'テストメディア']))
            ->assertStatus(200)
            ->assertJsonCount(1, 'media')
            ->assertJsonPath('media.0.title', 'テストメディアMV')
            ->assertJsonPath('media.0.publishedAt', '2024-03-01T12:00:00+09:00')
            ->assertJsonPath('maxPage', 1);
    }

    #[Test]
    public function canSearchWithoutTitle(): void
    {
        $repository = $this->app->make(MediaRepository::class);
        $repository->save($this->createMedia($this->generateUuid(), 'テストメディアMV', 'https://example.com/mv', MediaType::Mv, true));

        $this->withAuth()
            ->getJson(route(MediaRouteMap::Search, ['per_page' => 25]))
            ->assertStatus(200)
            ->assertJsonCount(1, 'media')
            ->assertJsonPath('media.0.title', 'テストメディアMV')
            ->assertJsonPath('maxPage', 1);
    }

    #[Test]
    public function canSearchByTypeAndDisplay(): void
    {
        $repository = $this->app->make(MediaRepository::class);
        $repository->save($this->createMedia($this->generateUuid(), 'MV', 'https://example.com/mv', MediaType::Mv, true));
        $repository->save($this->createMedia($this->generateUuid(), '非表示MV', 'https://example.com/hidden', MediaType::Mv, false));
        $repository->save($this->createMedia($this->generateUuid(), '配信', 'https://example.com/stream', MediaType::LiveStream, true));

        $this->withAuth()
            ->getJson(route(MediaRouteMap::Search, [
                'type' => MediaType::Mv->value,
                'is_display' => 'true',
            ]))
            ->assertStatus(200)
            ->assertJsonCount(1, 'media')
            ->assertJsonPath('media.0.title', 'MV')
            ->assertJsonPath('media.0.type.value', MediaType::Mv->value)
            ->assertJsonPath('media.0.isDisplay', true)
            ->assertJsonPath('maxPage', 1);
    }

    #[Test]
    public function canSortByPublishedAtDescending(): void
    {
        $repository = $this->app->make(MediaRepository::class);
        $olderId = $this->generateUuid();
        $newerId = $this->generateUuid();
        $repository->save($this->createMedia($olderId, '古い動画', 'https://example.com/old', MediaType::Mv, true, new DateTimeImmutable('2024-01-01 00:00:00')));
        $repository->save($this->createMedia($newerId, '新しい動画', 'https://example.com/new', MediaType::Mv, true, new DateTimeImmutable('2024-03-01 12:00:00')));

        $this->withAuth()
            ->getJson(route(MediaRouteMap::Search, ['sort' => 'published_at', 'order' => 'desc']))
            ->assertStatus(200)
            ->assertJsonPath('media.0.mediaId', $newerId)
            ->assertJsonPath('media.1.mediaId', $olderId);
    }

    #[Test]
    public function canSortByTitle(): void
    {
        $repository = $this->app->make(MediaRepository::class);
        $idA = $this->generateUuid();
        $idI = $this->generateUuid();
        $repository->save($this->createMedia($idI, 'い動画', 'https://example.com/i', MediaType::Mv, true));
        $repository->save($this->createMedia($idA, 'あ動画', 'https://example.com/a', MediaType::Mv, true));

        $this->withAuth()
            ->getJson(route(MediaRouteMap::Search, ['sort' => 'title', 'order' => 'asc']))
            ->assertStatus(200)
            ->assertJsonPath('media.0.mediaId', $idA)
            ->assertJsonPath('media.1.mediaId', $idI);
    }
}
