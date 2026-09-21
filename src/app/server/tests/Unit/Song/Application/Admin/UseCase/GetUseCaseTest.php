<?php

declare(strict_types=1);

namespace Tests\Unit\Song\Application\Admin\UseCase;

use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Song\Application\Admin\Assemble\AssembledPerson;
use Song\Application\Admin\Assemble\AssembledSong;
use Song\Application\Admin\Assemble\SongAssembler;
use Song\Application\Admin\UseCase\Get\GetInputData;
use Song\Application\Admin\UseCase\Get\GetUseCase;
use Song\Domain\Models\Persons\SongPersonRole;
use Song\Domain\Models\SongId;
use Song\Domain\Models\SongRepositoryInterface;
use Song\Domain\Models\SongType;
use Support\UseCase\Exceptions\ResourceNotFoundException;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class GetUseCaseTest extends TestCase
{
    use EntityFactory;

    private MockInterface&SongRepositoryInterface $repository;

    private MockInterface&SongAssembler $assembler;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(SongRepositoryInterface::class);
        $this->assembler = Mockery::mock(SongAssembler::class);
    }

    #[Test]
    public function getSong(): void
    {
        $songId = 'DDDDDDDD-DDDD-DDDD-DDDD-DDDDDDDDDDDD';
        $lyricistId = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $composerId = 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB';
        $arrangerId = 'CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC';

        $song = $this->createSong(
            $songId,
            'テスト楽曲',
            'テスト楽曲説明',
            SongType::Original,
            true,
            1,
            [],
            [
                ['personId' => $lyricistId, 'role' => 1, 'orderNo' => 1],
                ['personId' => $composerId, 'role' => 2, 'orderNo' => 2],
                ['personId' => $arrangerId, 'role' => 3, 'orderNo' => 3],
            ],
        );

        $this->repository->shouldReceive('find')
            ->withArgs(static fn (SongId $arg): bool => $arg->value === $songId)
            ->andReturn($song)
            ->once();

        $this->assembler->shouldReceive('assemble')
            ->with($song)
            ->andReturn(
                new AssembledSong(
                    $song->songId->value,
                    $song->title->value,
                    $song->description->value,
                    $song->lyricsLink?->value,
                    $song->type->getName(),
                    $song->type->value,
                    $song->isDisplay,
                    $song->orderNo->value,
                    [
                        new AssembledPerson($lyricistId, 'テスト作詞者A', SongPersonRole::Lyricist, 1),
                        new AssembledPerson($composerId, 'テスト作曲者A', SongPersonRole::Composer, 2),
                        new AssembledPerson($arrangerId, 'テスト編曲者A', SongPersonRole::Arranger, 3),
                    ],
                ),
            )
            ->once();

        $result = $this->getInstance()->handle(new GetInputData($songId));

        $response = $result;

        $this->assertSame($songId, $response->song->songId);
        $this->assertSame('テスト楽曲', $response->song->title);
        $this->assertSame('テスト楽曲説明', $response->song->description);
        $this->assertSame(SongType::Original->getName(), $response->song->typeName);
        $this->assertSame(SongType::Original->value, $response->song->typeValue);
        $this->assertTrue($response->song->isDisplay);
        $this->assertSame(1, $response->song->orderNo);

        $this->assertCount(3, $response->song->persons);
        $this->assertSame($lyricistId, $response->song->persons[0]->personId);
        $this->assertSame('テスト作詞者A', $response->song->persons[0]->name);
        $this->assertSame(SongPersonRole::Lyricist, $response->song->persons[0]->role);
        $this->assertSame($composerId, $response->song->persons[1]->personId);
        $this->assertSame(SongPersonRole::Composer, $response->song->persons[1]->role);
        $this->assertSame($arrangerId, $response->song->persons[2]->personId);
        $this->assertSame(SongPersonRole::Arranger, $response->song->persons[2]->role);
    }

    #[Test]
    public function failureGetSong(): void
    {
        $songId = 'DDDDDDDD-DDDD-DDDD-DDDD-DDDDDDDDDDDD';

        $this->repository->shouldReceive('find')
            ->withArgs(static fn (SongId $arg): bool => $arg->value === $songId)
            ->andReturnNull()
            ->once();

        $this->expectException(ResourceNotFoundException::class);

        $result = $this->getInstance()->handle(new GetInputData($songId));
    }

    private function getInstance(): GetUseCase
    {
        return new GetUseCase(
            $this->authorizer(),
            $this->repository,
            $this->assembler,
        );
    }
}
