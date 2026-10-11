<?php

declare(strict_types=1);

namespace Tests\Unit\Song\Domain\Services;

use Media\Domain\Models\MediaRepositoryInterface;
use Mockery;
use Mockery\MockInterface;
use Override;
use Person\Domain\Models\PersonId;
use Person\Domain\Models\PersonRepositoryInterface;
use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\SongRepositoryInterface;
use Song\Domain\Models\SongType;
use Song\Domain\Models\Tag\SongTagRepositoryInterface;
use Song\Domain\Services\SongIntegrityService;
use Support\Contracts\Uuid\UuidGeneratorInterface;
use Support\Domain\Exceptions\BusinessRuleViolationException;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class SongIntegrityServiceTest extends TestCase
{
    use EntityFactory;

    private MockInterface&UuidGeneratorInterface $generator;

    private MockInterface&PersonRepositoryInterface $personRepository;

    private MockInterface&SongRepositoryInterface $songRepository;

    private MockInterface&SongTagRepositoryInterface $songTagRepository;

    private MediaRepositoryInterface&MockInterface $mediaRepository;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->generator = Mockery::mock(UuidGeneratorInterface::class);
        $this->personRepository = Mockery::mock(PersonRepositoryInterface::class);
        $this->songRepository = Mockery::mock(SongRepositoryInterface::class);
        $this->songTagRepository = Mockery::mock(SongTagRepositoryInterface::class);
        $this->mediaRepository = Mockery::mock(MediaRepositoryInterface::class);
    }

    #[Test]
    public function prepareForCreate(): void
    {
        $title = 'テスト楽曲';
        $uuid = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $type = SongType::Original->value;
        $isDisplay = true;
        $currentMaxOrderNo = 100;
        $expectedOrderNo = 110;
        $description = '説明';
        $persons = [
            ['personId' => $lyricistId = 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', 'role' => 1, 'orderNo' => 1],
            ['personId' => $composerId = 'CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC', 'role' => 2, 'orderNo' => 2],
            ['personId' => $arrangerId = 'DDDDDDDD-DDDD-DDDD-DDDD-DDDDDDDDDDDD', 'role' => 3, 'orderNo' => 3],
        ];

        $expectedSong = $this->createSong(
            $uuid,
            $title,
            $description,
            SongType::Original,
            true,
            $expectedOrderNo,
            [],
            $persons,
        );

        $this->personRepository->shouldReceive('findByIds')
            ->withArgs(
                static fn (
                    PersonId $arg1,
                    PersonId $arg2,
                    PersonId $arg3,
                ): bool => $arg1->value === $lyricistId
                    && $arg2->value === $composerId
                    && $arg3->value === $arrangerId,
            )
            ->andReturn([
                $this->createPerson($lyricistId, '', 1),
                $this->createPerson($composerId, '', 1),
                $this->createPerson($arrangerId, '', 1),
            ])
            ->once();

        $this->generator->shouldReceive('generate')
            ->with()
            ->andReturn($uuid)
            ->once();

        $this->songRepository->shouldReceive('getMaxOrderNo')
            ->with()
            ->andReturn($currentMaxOrderNo)
            ->once();

        $result = $this->getInstance()->prepareForCreate(
            $title,
            $description,
            null,
            $type,
            $isDisplay,
            [],
            $persons,
            [],
        );

        $this->assertEquals($expectedSong, $result);
    }

    #[Test]
    public function prepareForCreateNotExistsPerson(): void
    {
        $title = 'テスト楽曲';
        $type = SongType::Original->value;
        $isDisplay = true;
        $description = '説明';
        $persons = [
            ['personId' => 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', 'role' => 1, 'orderNo' => 1],
            ['personId' => 'CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC', 'role' => 2, 'orderNo' => 2],
            ['personId' => 'DDDDDDDD-DDDD-DDDD-DDDD-DDDDDDDDDDDD', 'role' => 3, 'orderNo' => 3],
        ];

        $this->personRepository->shouldReceive('findByIds')
            ->withArgs(
                static fn (
                    PersonId $arg1,
                    PersonId $arg2,
                    PersonId $arg3,
                ): bool => $arg1->value === 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'
                    && $arg2->value === 'CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC'
                    && $arg3->value === 'DDDDDDDD-DDDD-DDDD-DDDD-DDDDDDDDDDDD',
            )
            ->andReturn([
                $this->createPerson('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', '', 1),
                $this->createPerson('CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC', '', 1),
            ])
            ->once();

        $this->expectException(BusinessRuleViolationException::class);

        $result = $this->getInstance()->prepareForCreate(
            $title,
            $description,
            null,
            $type,
            $isDisplay,
            [],
            $persons,
            [],
        );
    }

    #[Test]
    public function prepareForUpdate(): void
    {
        $songId = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $title = 'テスト楽曲';
        $description = '説明';
        $type = SongType::Original->value;
        $isDisplay = true;
        $orderNo = 1;
        $persons = [
            ['personId' => $lyricistId = 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', 'role' => 1, 'orderNo' => 1],
            ['personId' => $composerId = 'CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC', 'role' => 2, 'orderNo' => 2],
            ['personId' => $arrangerId = 'DDDDDDDD-DDDD-DDDD-DDDD-DDDDDDDDDDDD', 'role' => 3, 'orderNo' => 3],
        ];

        $expectedSong = $this->createSong(
            $songId,
            $title,
            $description,
            SongType::Original,
            true,
            $orderNo,
            [],
            $persons,
        );

        $this->personRepository->shouldReceive('findByIds')
            ->withArgs(
                static fn (
                    PersonId $arg1,
                    PersonId $arg2,
                    PersonId $arg3,
                ): bool => $arg1->value === $lyricistId
                    && $arg2->value === $composerId
                    && $arg3->value === $arrangerId,
            )
            ->andReturn([
                $this->createPerson($lyricistId, '', 1),
                $this->createPerson($composerId, '', 1),
                $this->createPerson($arrangerId, '', 1),
            ])
            ->once();

        $result = $this->getInstance()->prepareForUpdate(
            $songId,
            $title,
            $description,
            null,
            $type,
            $isDisplay,
            $orderNo,
            [],
            $persons,
            [],
        );

        $this->assertEquals($expectedSong, $result);
    }

    #[Test]
    public function prepareForUpdateNotExistsPerson(): void
    {
        $songId = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $title = 'テスト楽曲';
        $description = '説明';
        $type = SongType::Original->value;
        $isDisplay = true;
        $orderNo = 1;
        $persons = [
            ['personId' => 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', 'role' => 1, 'orderNo' => 1],
            ['personId' => 'CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC', 'role' => 2, 'orderNo' => 2],
            ['personId' => 'DDDDDDDD-DDDD-DDDD-DDDD-DDDDDDDDDDDD', 'role' => 3, 'orderNo' => 3],
        ];

        $this->personRepository->shouldReceive('findByIds')
            ->withArgs(
                static fn (
                    PersonId $arg1,
                    PersonId $arg2,
                    PersonId $arg3,
                ): bool => $arg1->value === 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'
                    && $arg2->value === 'CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC'
                    && $arg3->value === 'DDDDDDDD-DDDD-DDDD-DDDD-DDDDDDDDDDDD',
            )
            ->andReturn([
                $this->createPerson('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', '', 1),
                $this->createPerson('CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC', '', 1),
            ])
            ->once();

        $this->expectException(BusinessRuleViolationException::class);

        $result = $this->getInstance()->prepareForUpdate(
            $songId,
            $title,
            $description,
            null,
            $type,
            $isDisplay,
            $orderNo,
            [],
            $persons,
            [],
        );
    }

    private function getInstance(): SongIntegrityService
    {
        return new SongIntegrityService(
            $this->generator,
            $this->songRepository,
            $this->personRepository,
            $this->songTagRepository,
            $this->mediaRepository,
        );
    }
}
