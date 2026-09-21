<?php

declare(strict_types=1);

namespace Tests\Integration\Release\Application\Admin\UseCase\Update;

use PHPUnit\Framework\Attributes\Test;
use Release\Application\Admin\UseCase\Update\UpdateInputData;
use Release\Application\Admin\UseCase\Update\UpdateUseCase;
use Release\Domain\Models\ReleaseFormat;
use Release\Domain\Models\ReleaseGroupType;
use Song\Domain\Models\SongType;
use Support\Domain\Exceptions\BusinessRuleViolationException;
use Support\UseCase\Exceptions\ResourceNotFoundException;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class UpdateUseCaseTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function canUpdate(): void
    {
        $songId1 = $this->generateUuid();
        $songId2 = $this->generateUuid();
        $releaseGroupId = $this->generateUuid();
        $releaseId = $this->generateUuid();

        $this->storeSongs(
            $this->createSong($songId1, 'テスト楽曲1', '説明', SongType::Original, true, 10),
            $this->createSong($songId2, 'テスト楽曲2', '説明', SongType::Original, true, 20),
        );
        $this->storeReleaseGroups(
            $this->createReleaseGroup($releaseGroupId, '観測された春', ReleaseGroupType::Album, true),
        );
        $this->storeReleases(
            $this->createRelease(
                $releaseId,
                $releaseGroupId,
                '旧版名',
                true,
                formats: [ReleaseFormat::Cd->value],
                media: [
                    [
                        'position' => 1,
                        'name' => null,
                        'tracks' => [['songId' => $songId1, 'title' => null, 'trackNo' => 1]],
                    ],
                ],
            ),
        );

        $result = $this->getInstance()->handle(new UpdateInputData(
            releaseId: $releaseId,
            name: '新版名',
            releasedOn: '2026-05-09',
            description: '更新後の説明',
            color: '#2e62a0',
            isDisplay: false,
            orderNo: 20,
            formatValues: [ReleaseFormat::Cd->value],
            media: [
                [
                    'position' => 1,
                    'name' => null,
                    'tracks' => [
                        ['songId' => $songId2, 'title' => null, 'trackNo' => 1],
                        ['songId' => $songId1, 'title' => null, 'trackNo' => 2],
                    ],
                ],
            ],
        ));

        $this->assertSame('新版名', $result->release->name->value);
        // 所属先グループは更新で変わらない
        $this->assertSame($releaseGroupId, $result->release->releaseGroupId->value);
        $this->assertSame(20, $result->release->orderNo->value);

        $this->assertDatabaseHas('releases', [
            'name' => '新版名',
            'description' => '更新後の説明',
            'color' => '#2e62a0',
            'is_display' => false,
            'order_no' => 20,
        ]);
        $this->assertDatabaseCount('release_media', 1);
        $this->assertDatabaseCount('release_tracks', 2);
    }

    #[Test]
    public function canUpdateWithTitleOnlyTrack(): void
    {
        $songId = $this->generateUuid();
        $releaseGroupId = $this->generateUuid();
        $releaseId = $this->generateUuid();

        $this->storeSongs(
            $this->createSong($songId, 'テスト楽曲1', '説明', SongType::Original, true, 10),
        );
        $this->storeReleaseGroups(
            $this->createReleaseGroup($releaseGroupId, '観測された春', ReleaseGroupType::Album, true),
        );
        $this->storeReleases(
            $this->createRelease(
                $releaseId,
                $releaseGroupId,
                '旧版名',
                true,
                formats: [ReleaseFormat::Cd->value],
                media: [
                    [
                        'position' => 1,
                        'name' => null,
                        'tracks' => [['songId' => $songId, 'title' => null, 'trackNo' => 1]],
                    ],
                ],
            ),
        );

        $result = $this->getInstance()->handle(new UpdateInputData(
            releaseId: $releaseId,
            name: '新版名',
            releasedOn: '2026-05-09',
            description: '説明',
            color: '#989899',
            isDisplay: true,
            orderNo: 1,
            formatValues: [ReleaseFormat::Cd->value],
            media: [
                [
                    'position' => 1,
                    'name' => null,
                    'tracks' => [
                        ['songId' => $songId, 'title' => null, 'trackNo' => 1],
                        ['songId' => null, 'title' => '管理対象外の楽曲', 'trackNo' => 2],
                    ],
                ],
            ],
        ));

        $this->assertDatabaseCount('release_tracks', 2);
        $this->assertDatabaseHas('release_tracks', [
            'track_no' => 2,
            'song_id' => null,
            'title' => '管理対象外の楽曲',
        ]);
    }

    #[Test]
    public function notFound(): void
    {
        $this->expectException(ResourceNotFoundException::class);

        $result = $this->getInstance()->handle(new UpdateInputData(
            releaseId: $this->generateUuid(),
            name: '新版名',
            releasedOn: '2026-05-09',
            description: '説明',
            color: '#989899',
            isDisplay: true,
            orderNo: 1,
            formatValues: [ReleaseFormat::Cd->value],
            media: [],
        ));
    }

    #[Test]
    public function updateFailsWhenTrackNosAreDuplicatedInMedium(): void
    {
        $songId1 = $this->generateUuid();
        $songId2 = $this->generateUuid();
        $releaseGroupId = $this->generateUuid();
        $releaseId = $this->generateUuid();

        $this->storeSongs(
            $this->createSong($songId1, 'テスト楽曲1', '説明', SongType::Original, true, 10),
            $this->createSong($songId2, 'テスト楽曲2', '説明', SongType::Original, true, 20),
        );
        $this->storeReleaseGroups(
            $this->createReleaseGroup($releaseGroupId, '観測された春', ReleaseGroupType::Album, true),
        );
        $this->storeReleases(
            $this->createRelease($releaseId, $releaseGroupId, '旧版名', true),
        );

        $this->expectException(BusinessRuleViolationException::class);

        $result = $this->getInstance()->handle(new UpdateInputData(
            releaseId: $releaseId,
            name: '新版名',
            releasedOn: '2026-05-09',
            description: '説明',
            color: '#989899',
            isDisplay: true,
            orderNo: 1,
            formatValues: [ReleaseFormat::Cd->value],
            media: [
                [
                    'position' => 1,
                    'name' => null,
                    'tracks' => [
                        ['songId' => $songId1, 'title' => null, 'trackNo' => 1],
                        ['songId' => $songId2, 'title' => null, 'trackNo' => 1],
                    ],
                ],
            ],
        ));
    }

    private function getInstance(): UpdateUseCase
    {
        $this->privilegedContext();

        return $this->app->make(UpdateUseCase::class);
    }
}
