<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Media;

use DateTimeImmutable;
use Media\Domain\Models\MediaType;
use Media\Infrastructures\MediaRepository;
use Media\Route\MediaRouteMap;
use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\SongType;
use Song\Infrastructures\SongRepository;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class GetMediaTest extends DatabaseTestCase
{
    use EntityFactory;
    use WithAuth;

    #[Test]
    public function found(): void
    {
        $uuid = $this->generateUuid();
        $songId = $this->generateUuid();

        $repository = $this->app->make(MediaRepository::class);
        $repository->save(
            $this->createMedia(
                $uuid,
                'テストメディアMV',
                'https://example.com/media',
                MediaType::Mv,
                true,
                new DateTimeImmutable('2024-03-01 12:00:00'),
            ),
        );
        $this->app->make(SongRepository::class)->save(
            $this->createSong(
                $songId,
                'テスト楽曲',
                '説明',
                SongType::Original,
                true,
                10,
                [],
                [],
                [],
                [],
                [],
                [['mediaId' => $uuid, 'orderNo' => 2]],
            ),
        );

        $this->withAuth()
            ->getJson(route(MediaRouteMap::Get, $uuid))
            ->assertStatus(200)
            ->assertExactJson([
                'media' => [
                    'mediaId' => $uuid,
                    'title' => 'テストメディアMV',
                    'url' => 'https://example.com/media',
                    'publishedAt' => '2024-03-01T12:00:00+09:00',
                    'type' => [
                        'name' => MediaType::Mv->getName(),
                        'value' => MediaType::Mv->value,
                    ],
                    'isDisplay' => true,
                ],
                'songs' => [[
                    'songId' => $songId,
                    'title' => 'テスト楽曲',
                    'songOrderNo' => 10,
                    'mediaOrderNo' => 2,
                ]],
            ]);
    }

    #[Test]
    public function notFound(): void
    {
        $this->withAuth()
            ->getJson(route(MediaRouteMap::Get, $this->generateUuid()))
            ->assertStatus(404);
    }
}
