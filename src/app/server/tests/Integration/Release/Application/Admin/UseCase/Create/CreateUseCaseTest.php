<?php

declare(strict_types=1);

namespace Tests\Integration\Release\Application\Admin\UseCase\Create;

use PHPUnit\Framework\Attributes\Test;
use Release\Application\Admin\UseCase\Create\CreateInputData;
use Release\Application\Admin\UseCase\Create\CreateUseCase;
use Release\Domain\Models\ReleaseFormat;
use Release\Domain\Models\ReleaseGroupType;
use Song\Domain\Models\SongType;
use Support\Domain\Exceptions\BusinessRuleViolationException;
use Support\Domain\Exceptions\InvalidDomainException;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class CreateUseCaseTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function canCreate(): void
    {
        $songId = $this->generateUuid();
        $releaseGroupId = $this->generateUuid();

        $this->storeSongs(
            $this->createSong($songId, 'テスト楽曲1', '説明', SongType::Original, true, 1),
        );
        $this->storeReleaseGroups(
            $this->createReleaseGroup($releaseGroupId, '観測された春', ReleaseGroupType::Album, true),
        );

        $result = $this->getInstance()->handle(new CreateInputData(
            releaseGroupId: $releaseGroupId,
            name: '初回限定盤',
            releasedOn: '2026-05-09',
            description: '',
            color: '#4a5a78',
            isDisplay: true,
            orderNo: 10,
            formatValues: [ReleaseFormat::Cd->value],
            media: [
                [
                    'position' => 1,
                    'name' => null,
                    'tracks' => [['songId' => $songId, 'title' => null, 'trackNo' => 1]],
                ],
                [
                    'position' => 2,
                    'name' => null,
                    'tracks' => [],
                ],
            ],
        ));

        $this->assertSame('初回限定盤', $result->release->name->value);
        $this->assertSame($releaseGroupId, $result->release->releaseGroupId->value);
        $this->assertSame(10, $result->release->orderNo->value);
        $this->assertCount(2, $result->release->media->toGeneric());

        $this->assertDatabaseHas('releases', [
            'name' => '初回限定盤',
            'description' => '',
            'color' => '#4a5a78',
            'is_display' => true,
            'order_no' => 10,
        ]);
        $this->assertDatabaseCount('release_media', 2);
        $this->assertDatabaseCount('release_tracks', 1);
    }

    #[Test]
    public function canCreateWithEmptyName(): void
    {
        $releaseGroupId = $this->generateUuid();

        $this->storeReleaseGroups(
            $this->createReleaseGroup($releaseGroupId, '観測された春', ReleaseGroupType::Album, true),
        );

        $result = $this->getInstance()->handle(new CreateInputData(
            releaseGroupId: $releaseGroupId,
            name: '',
            releasedOn: '2026-05-09',
            description: '',
            color: '#4a5a78',
            isDisplay: true,
            orderNo: 1,
            formatValues: [ReleaseFormat::Digital->value],
            media: [],
        ));

        $this->assertSame('', $result->release->name->value);
        $this->assertDatabaseHas('releases', [
            'name' => '',
            'color' => '#4a5a78',
            'is_display' => true,
            'order_no' => 1,
        ]);
    }

    #[Test]
    public function canCreateWithTitleOnlyTrack(): void
    {
        $songId = $this->generateUuid();
        $releaseGroupId = $this->generateUuid();

        $this->storeSongs(
            $this->createSong($songId, 'テスト楽曲1', '説明', SongType::Original, true, 1),
        );
        $this->storeReleaseGroups(
            $this->createReleaseGroup($releaseGroupId, '観測された春', ReleaseGroupType::Album, true),
        );

        $result = $this->getInstance()->handle(new CreateInputData(
            releaseGroupId: $releaseGroupId,
            name: '初回限定盤',
            releasedOn: '2026-05-09',
            description: '',
            color: '#989899',
            isDisplay: true,
            orderNo: 10,
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
    public function canCreateWithOverriddenTrackTitle(): void
    {
        $songId = $this->generateUuid();
        $releaseGroupId = $this->generateUuid();

        $this->storeSongs(
            $this->createSong($songId, 'テスト楽曲1', '説明', SongType::Original, true, 1),
        );
        $this->storeReleaseGroups(
            $this->createReleaseGroup($releaseGroupId, '観測された春', ReleaseGroupType::Album, true),
        );

        // 楽曲への紐づきを維持したまま表示名だけを上書きするケース
        $result = $this->getInstance()->handle(new CreateInputData(
            releaseGroupId: $releaseGroupId,
            name: '初回限定盤',
            releasedOn: '2026-05-09',
            description: '',
            color: '#989899',
            isDisplay: true,
            orderNo: 10,
            formatValues: [ReleaseFormat::Cd->value],
            media: [
                [
                    'position' => 1,
                    'name' => null,
                    'tracks' => [['songId' => $songId, 'title' => 'テスト楽曲1 -instrumental-', 'trackNo' => 1]],
                ],
            ],
        ));

        $this->assertDatabaseHas('release_tracks', [
            'track_no' => 1,
            'title' => 'テスト楽曲1 -instrumental-',
        ]);
    }

    #[Test]
    public function createFailsWhenTrackHasNeitherSongIdNorTitle(): void
    {
        $releaseGroupId = $this->generateUuid();

        $this->storeReleaseGroups(
            $this->createReleaseGroup($releaseGroupId, '観測された春', ReleaseGroupType::Album, true),
        );

        $this->expectException(BusinessRuleViolationException::class);

        $result = $this->getInstance()->handle(new CreateInputData(
            releaseGroupId: $releaseGroupId,
            name: '初回限定盤',
            releasedOn: '2026-05-09',
            description: '',
            color: '#989899',
            isDisplay: true,
            orderNo: 10,
            formatValues: [ReleaseFormat::Cd->value],
            media: [
                [
                    'position' => 1,
                    'name' => null,
                    'tracks' => [['songId' => null, 'title' => null, 'trackNo' => 1]],
                ],
            ],
        ));
    }

    #[Test]
    public function createFailsWhenReleaseGroupDoesNotExist(): void
    {
        $this->expectException(BusinessRuleViolationException::class);

        $result = $this->getInstance()->handle(new CreateInputData(
            releaseGroupId: $this->generateUuid(),
            name: '通常盤',
            releasedOn: '2026-05-09',
            description: '',
            color: '#989899',
            isDisplay: true,
            orderNo: 1,
            formatValues: [ReleaseFormat::Cd->value],
            media: [],
        ));
    }

    #[Test]
    public function createFailsWhenReleasedOnIsInvalid(): void
    {
        $releaseGroupId = $this->generateUuid();

        $this->storeReleaseGroups(
            $this->createReleaseGroup($releaseGroupId, '観測された春', ReleaseGroupType::Album, true),
        );

        $this->expectException(InvalidDomainException::class);

        $result = $this->getInstance()->handle(new CreateInputData(
            releaseGroupId: $releaseGroupId,
            name: '通常盤',
            releasedOn: 'invalid-date',
            description: '説明',
            color: '#989899',
            isDisplay: true,
            orderNo: 1,
            formatValues: [ReleaseFormat::Cd->value],
            media: [],
        ));
    }

    private function getInstance(): CreateUseCase
    {
        $this->privilegedContext();

        return $this->app->make(CreateUseCase::class);
    }
}
