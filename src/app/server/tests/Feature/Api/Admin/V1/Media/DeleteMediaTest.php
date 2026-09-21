<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Media;

use Media\Domain\Models\MediaType;
use Media\Infrastructures\MediaRepository;
use Media\Route\MediaRouteMap;
use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\SongType;
use Song\Infrastructures\SongRepository;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class DeleteMediaTest extends DatabaseTestCase
{
    use EntityFactory;
    use WithAuth;

    #[Test]
    public function canDelete(): void
    {
        $uuid = $this->generateUuid();

        $repository = $this->app->make(MediaRepository::class);
        $media = $this->createMedia($uuid, 'テストメディアMV', 'https://example.com/media', MediaType::Mv, true);
        $repository->save($media);

        $this->withAuth()
            ->delete(route(MediaRouteMap::Delete, $uuid))
            ->assertStatus(204);

        $this->assertNull($repository->find($media->mediaId));
    }

    #[Test]
    public function cannotDeleteWhenUsedInSong(): void
    {
        $mediaId = $this->generateUuid();

        $repository = $this->app->make(MediaRepository::class);
        $media = $this->createMedia($mediaId, 'テストメディアMV', 'https://example.com/media', MediaType::Mv, true);
        $repository->save($media);

        $this->app->make(SongRepository::class)->save(
            $this->createSong(
                $this->generateUuid(),
                '曲名',
                '説明',
                null,
                SongType::Original,
                true,
                1,
                [],
                [],
                [],
                [
                    ['mediaId' => $mediaId, 'orderNo' => 1],
                ],
            ),
        );

        $this->withAuth()
            ->delete(route(MediaRouteMap::Delete, $mediaId))
            ->assertStatus(400)
            ->assertExactJson([
                'code' => 'business_rule_violation',
                'message' => 'このメディアは楽曲に使用されているため削除できません',
            ]);

        $this->assertNotNull($repository->find($media->mediaId));
    }

    #[Test]
    public function canDeleteEvenIfTargetDoesNotExist(): void
    {
        $this->withAuth()
            ->delete(route(MediaRouteMap::Delete, 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'))
            ->assertStatus(204);
    }
}
