<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Song;

use PHPUnit\Framework\Attributes\Test;
use Release\Domain\Models\ReleaseGroupType;
use Song\Domain\Models\SongId;
use Song\Domain\Models\SongType;
use Song\Infrastructures\SongRepository;
use Song\Route\SongRouteMap;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class DeleteSongTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;
    use WithAuth;

    #[Test]
    public function canDelete(): void
    {
        $uuid = $this->generateUuid();

        $this->app
            ->make(SongRepository::class)
            ->save($this->createSong($uuid, '', '', SongType::Original, true, 1, [], [], [], []));

        $this
            ->withAuth()
            ->delete(route(SongRouteMap::Delete, $uuid))
            ->assertNoContent();
    }

    #[Test]
    public function cannotDeleteWhenUsedInReleaseTrack(): void
    {
        $songId = $this->generateUuid();
        $releaseGroupId = $this->generateUuid();

        $this->storeSongs($this->createSong($songId, '曲名', '説明', SongType::Original, true, 1));
        $this->storeReleaseGroups($this->createReleaseGroup($releaseGroupId, '作品', ReleaseGroupType::Album, true));
        $this->storeReleases($this->createRelease(
            $this->generateUuid(),
            $releaseGroupId,
            'リリース',
            true,
            media: [
                [
                    'position' => 1,
                    'name' => null,
                    'tracks' => [
                        ['songIds' => [$songId], 'title' => null, 'trackNo' => 1],
                    ],
                ],
            ],
        ));

        $this
            ->withAuth()
            ->delete(route(SongRouteMap::Delete, $songId))
            ->assertBadRequest()
            ->assertExactJson([
                'code' => 'business_rule_violation',
                'message' => 'この楽曲はイベントやリリースで使われているため削除できません',
            ]);

        $this->assertNotNull($this->app->make(SongRepository::class)->find(new SongId($songId)));
    }

    #[Test]
    public function invalidSongId(): void
    {
        $this
            ->withAuth()
            ->delete(route(SongRouteMap::Delete, 'invalid-id'))
            ->assertUnprocessable()
            ->assertExactJson([
                'code' => 'validation_failed',
                'message' => '入力内容に誤りがあります',
                'details' => [
                    [
                        'field' => '',
                        'message' => '予期せぬエラー',
                    ],
                ],
            ]);
    }
}
