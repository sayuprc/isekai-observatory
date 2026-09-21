<?php

declare(strict_types=1);

namespace Tests\Unit\Release\Domain\Services;

use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Release\Domain\Models\ReleaseFormat;
use Release\Domain\Models\ReleaseGroupId;
use Release\Domain\Models\ReleaseGroupRepositoryInterface;
use Release\Domain\Models\ReleaseGroupType;
use Release\Domain\Services\ReleaseIntegrityService;
use Song\Domain\Models\SongId;
use Song\Domain\Models\SongRepositoryInterface;
use Song\Domain\Models\SongType;
use Support\Contracts\Uuid\UuidGeneratorInterface;
use Support\Domain\Exceptions\BusinessRuleViolationException;
use Support\Domain\Exceptions\InvalidDomainException;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class ReleaseIntegrityServiceTest extends TestCase
{
    use EntityFactory;

    private const string RELEASE_ID = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';

    private const string RELEASE_GROUP_ID = 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB';

    private const string SONG_ID = 'CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC';

    private MockInterface&UuidGeneratorInterface $generator;

    private MockInterface&ReleaseGroupRepositoryInterface $releaseGroupRepository;

    private MockInterface&SongRepositoryInterface $songRepository;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->generator = Mockery::mock(UuidGeneratorInterface::class);
        $this->releaseGroupRepository = Mockery::mock(ReleaseGroupRepositoryInterface::class);
        $this->songRepository = Mockery::mock(SongRepositoryInterface::class);
    }

    #[Test]
    public function prepareForCreateSkipsSongExistenceCheckForTitleOnlyTracks(): void
    {
        $this->generator->shouldReceive('generate')
            ->with()
            ->andReturn(self::RELEASE_ID)
            ->once();

        $this->releaseGroupRepository->shouldReceive('find')
            ->withArgs(static fn (ReleaseGroupId $arg): bool => $arg->value === self::RELEASE_GROUP_ID)
            ->andReturn($this->createReleaseGroup(self::RELEASE_GROUP_ID, '観測された春', ReleaseGroupType::Album))
            ->once();

        // 参照トラックの 1 曲だけ存在確認される(タイトルのみトラックはスキップ)
        $this->songRepository->shouldReceive('find')
            ->withArgs(static fn (SongId $arg): bool => $arg->value === self::SONG_ID)
            ->andReturn($this->createSong(self::SONG_ID, 'テスト楽曲', '説明', SongType::Original, true, 10))
            ->once();

        $result = $this->getInstance()->prepareForCreate(
            self::RELEASE_GROUP_ID,
            '初回限定盤',
            '2024-01-01',
            '説明',
            '#989899',
            true,
            1,
            [ReleaseFormat::Cd->value],
            [
                [
                    'position' => 1,
                    'name' => null,
                    'tracks' => [
                        ['songId' => self::SONG_ID, 'title' => null, 'trackNo' => 1],
                        ['songId' => null, 'title' => '管理対象外の楽曲', 'trackNo' => 2],
                    ],
                ],
            ],
        );

        $this->assertSame([
            ['song_id' => self::SONG_ID, 'title' => null, 'track_no' => 1],
            ['song_id' => null, 'title' => '管理対象外の楽曲', 'track_no' => 2],
        ], $result->media->toArray()[0]['tracks']);
    }

    #[Test]
    public function prepareForCreateFailsWhenReferencedSongDoesNotExist(): void
    {
        $this->generator->shouldReceive('generate')
            ->with()
            ->andReturn(self::RELEASE_ID)
            ->once();

        $this->releaseGroupRepository->shouldReceive('find')
            ->withArgs(static fn (ReleaseGroupId $arg): bool => $arg->value === self::RELEASE_GROUP_ID)
            ->andReturn($this->createReleaseGroup(self::RELEASE_GROUP_ID, '観測された春', ReleaseGroupType::Album))
            ->once();

        $this->songRepository->shouldReceive('find')
            ->withArgs(static fn (SongId $arg): bool => $arg->value === self::SONG_ID)
            ->andReturn(null)
            ->once();

        $this->expectException(BusinessRuleViolationException::class);

        $result = $this->getInstance()->prepareForCreate(
            self::RELEASE_GROUP_ID,
            '初回限定盤',
            '2024-01-01',
            '説明',
            '#989899',
            true,
            1,
            [ReleaseFormat::Cd->value],
            [
                [
                    'position' => 1,
                    'name' => null,
                    'tracks' => [['songId' => self::SONG_ID, 'title' => null, 'trackNo' => 1]],
                ],
            ],
        );
    }

    #[Test]
    public function prepareForCreateFailsWhenFormatsAreEmpty(): void
    {
        $this->generator->shouldReceive('generate')
            ->with()
            ->andReturn(self::RELEASE_ID)
            ->once();

        $this->expectException(InvalidDomainException::class);

        $result = $this->getInstance()->prepareForCreate(
            self::RELEASE_GROUP_ID,
            '初回限定盤',
            '2024-01-01',
            '説明',
            '#989899',
            true,
            1,
            [],
            [],
        );
    }

    #[Test]
    public function prepareForCreateFailsWhenFormatsAreDuplicated(): void
    {
        $this->generator->shouldReceive('generate')
            ->with()
            ->andReturn(self::RELEASE_ID)
            ->once();

        $this->expectException(BusinessRuleViolationException::class);

        $result = $this->getInstance()->prepareForCreate(
            self::RELEASE_GROUP_ID,
            '初回限定盤',
            '2024-01-01',
            '説明',
            '#989899',
            true,
            1,
            [ReleaseFormat::Cd->value, ReleaseFormat::Cd->value],
            [],
        );
    }

    private function getInstance(): ReleaseIntegrityService
    {
        return new ReleaseIntegrityService(
            $this->generator,
            $this->releaseGroupRepository,
            $this->songRepository,
        );
    }
}
