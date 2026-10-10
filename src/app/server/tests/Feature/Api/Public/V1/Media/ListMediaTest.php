<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Public\V1\Media;

use DateTimeImmutable;
use Media\Domain\Models\MediaType;
use Media\Route\PublicMediaRouteMap;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class ListMediaTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function returnsPublicMediaOnly(): void
    {
        $mediaId = $this->generateUuid();
        $this->storeMedia(
            $this->createMedia($mediaId, '公開 MV', 'https://example.com/public', MediaType::Mv, true, new DateTimeImmutable('2024-03-01 12:00:00')),
            $this->createMedia($this->generateUuid(), '非公開 MV', 'https://example.com/private', MediaType::Mv, false),
        );

        $this->get(route(PublicMediaRouteMap::List))
            ->assertStatus(200)
            ->assertJsonCount(1, 'items')
            ->assertJsonPath('items.0.mediaId', $mediaId)
            ->assertJsonPath('items.0.title', '公開 MV')
            ->assertJsonPath('items.0.url', 'https://example.com/public')
            ->assertJsonPath('items.0.type', MediaType::Mv->value)
            ->assertJsonMissingPath('nextPageToken');
    }

    #[Test]
    public function paginatesInTitleOrderWithPageToken(): void
    {
        $firstId = $this->generateUuid();
        $secondId = $this->generateUuid();
        $this->storeMedia(
            $this->createMedia($secondId, 'B 配信', 'https://example.com/b', MediaType::LiveStream, true),
            $this->createMedia($firstId, 'A 配信', 'https://example.com/a', MediaType::LiveStream, true),
        );

        $first = $this->get(route(PublicMediaRouteMap::List, ['pageSize' => 1]))
            ->assertStatus(200)
            ->assertJsonPath('items.0.mediaId', $firstId);

        $this->get(route(PublicMediaRouteMap::List, ['pageSize' => 1, 'pageToken' => $first->json('nextPageToken')]))
            ->assertStatus(200)
            ->assertJsonPath('items.0.mediaId', $secondId)
            ->assertJsonMissingPath('nextPageToken');
    }
}
