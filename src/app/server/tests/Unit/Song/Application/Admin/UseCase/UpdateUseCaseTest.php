<?php

declare(strict_types=1);

namespace Tests\Unit\Song\Application\Admin\UseCase;

use Closure;
use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Song\Application\Admin\Assemble\AssembledPerson;
use Song\Application\Admin\Assemble\AssembledSong;
use Song\Application\Admin\Assemble\SongAssembler;
use Song\Application\Admin\UseCase\Update\UpdateInputData;
use Song\Application\Admin\UseCase\Update\UpdateUseCase;
use Song\Domain\Models\Persons\SongPersonRole;
use Song\Domain\Models\Song;
use Song\Domain\Models\SongId;
use Song\Domain\Models\SongRepositoryInterface;
use Song\Domain\Models\SongType;
use Song\Domain\Services\SongIntegrityService;
use Support\Contracts\TransactionInterface;
use Support\Domain\Exceptions\BusinessRuleViolationException;
use Support\UseCase\AuditLog\AuditLogRecorderInterface;
use Support\UseCase\Exceptions\ResourceNotFoundException;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class UpdateUseCaseTest extends TestCase
{
    use EntityFactory;

    private MockInterface&TransactionInterface $transaction;

    private MockInterface&SongRepositoryInterface $repository;

    private MockInterface&SongIntegrityService $service;

    private MockInterface&SongAssembler $assembler;

    private AuditLogRecorderInterface&MockInterface $recorder;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->transaction = Mockery::mock(TransactionInterface::class);
        $this->repository = Mockery::mock(SongRepositoryInterface::class);
        $this->service = Mockery::mock(SongIntegrityService::class);
        $this->assembler = Mockery::mock(SongAssembler::class);
        $this->recorder = Mockery::mock(AuditLogRecorderInterface::class);
        $this->recorder->shouldReceive('record')->byDefault();
    }

    #[Test]
    public function update(): void
    {
        $songId = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $title = 'テスト楽曲';
        $description = 'テスト楽曲説明';
        $lyricsLink = 'https://example.com/lyrics';
        $typeValue = SongType::Original->value;
        $isDisplay = false;
        $orderNo = 1;
        $persons = [
            ['personId' => $lyricistId = 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', 'role' => 1, 'orderNo' => 1],
            ['personId' => $composerId = 'CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC', 'role' => 2, 'orderNo' => 2],
            ['personId' => $arrangerId = 'DDDDDDDD-DDDD-DDDD-DDDD-DDDDDDDDDDDD', 'role' => 3, 'orderNo' => 3],
        ];

        $this->transaction->shouldReceive('scope')
            ->withArgs(static fn (Closure $_) => true)
            ->andReturnUsing(static fn (Closure $arg) => $arg())
            ->once();

        $this->repository->shouldReceive('find')
            ->withArgs(static fn (SongId $arg): bool => $arg->value === $songId)
            ->andReturn($this->createSong($songId, $title, $description, $lyricsLink, SongType::from($typeValue), true, 1))
            ->once();

        $this->service->shouldReceive('prepareForUpdate')
            ->with($songId, $title, $description, $lyricsLink, $typeValue, $isDisplay, $orderNo, [], $persons, [])
            ->andReturn(
                $song = $this->createSong(
                    $songId,
                    $title,
                    $description,
                    $lyricsLink,
                    SongType::from($typeValue),
                    $isDisplay,
                    $orderNo,
                    [],
                    $persons,
                ),
            )
            ->once();

        $this->repository->shouldReceive('save')
            ->withArgs(fn (Song $arg): bool => $this->assertSong($arg, $songId, $title, $description, $lyricsLink, $typeValue, $isDisplay, $orderNo, $persons))
            ->andReturn($song)
            ->once();

        $this->assembler->shouldReceive('assemble')
            ->withArgs(fn (Song $arg): bool => $this->assertSong($arg, $songId, $title, $description, $lyricsLink, $typeValue, $isDisplay, $orderNo, $persons))
            ->andReturn(
                new AssembledSong(
                    $song->songId->value,
                    $song->title->value,
                    $song->description->value,
                    $song->lyricsLink?->value,
                    $song->type->name,
                    $song->type->value,
                    $song->isDisplay,
                    $song->orderNo->value,
                    [
                        new AssembledPerson($lyricistId, 'テスト作詞者', SongPersonRole::Lyricist, 1),
                        new AssembledPerson($composerId, 'テスト作曲者', SongPersonRole::Composer, 2),
                        new AssembledPerson($arrangerId, 'テスト編曲者', SongPersonRole::Arranger, 3),
                    ],
                ),
            )
            ->once();

        $result = $this->getInstance()->handle(
            new UpdateInputData(
                $songId,
                $title,
                $description,
                $lyricsLink,
                $typeValue,
                $isDisplay,
                $orderNo,
                [],
                $persons,
            ),
        );
    }

    #[Test]
    public function updateFailsIfPersonIdNotExists(): void
    {
        $songId = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $title = '曲名';
        $description = '説明';
        $lyricsLink = null;
        $typeValue = 1;
        $isDisplay = false;
        $orderNo = 1;
        $persons = [
            ['personId' => 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', 'role' => 1, 'orderNo' => 1],
            ['personId' => 'CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC', 'role' => 2, 'orderNo' => 2],
            ['personId' => 'DDDDDDDD-DDDD-DDDD-DDDD-DDDDDDDDDDDD', 'role' => 3, 'orderNo' => 3],
        ];

        $this->transaction->shouldReceive('scope')
            ->withArgs(static fn (Closure $_) => true)
            ->andReturnUsing(static fn (Closure $arg) => $arg())
            ->once();

        $this->repository->shouldReceive('find')
            ->withArgs(static fn (SongId $arg): bool => $arg->value === $songId)
            ->andReturn($this->createSong($songId, $title, $description, $lyricsLink, SongType::Original, true, 1))
            ->once();

        $this->service->shouldReceive('prepareForUpdate')
            ->with($songId, $title, $description, $lyricsLink, $typeValue, $isDisplay, $orderNo, [], $persons, [])
            ->andThrow(new BusinessRuleViolationException('検証エラー'))
            ->once();

        $this->expectException(BusinessRuleViolationException::class);

        $result = $this->getInstance()->handle(
            new UpdateInputData(
                $songId,
                $title,
                $description,
                $lyricsLink,
                $typeValue,
                $isDisplay,
                $orderNo,
                [],
                $persons,
            ),
        );
    }

    #[Test]
    public function updateFailsIfSongDoesNotExist(): void
    {
        $songId = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';

        $this->transaction->shouldReceive('scope')
            ->withArgs(static fn (Closure $_) => true)
            ->andReturnUsing(static fn (Closure $arg) => $arg())
            ->once();

        $this->repository->shouldReceive('find')
            ->withArgs(static fn (SongId $arg): bool => $arg->value === $songId)
            ->andReturnNull()
            ->once();

        $this->service->shouldNotReceive('prepareForUpdate');
        $this->repository->shouldNotReceive('save');

        $this->expectException(ResourceNotFoundException::class);

        $result = $this->getInstance()->handle(
            new UpdateInputData(
                $songId,
                '曲名',
                '説明',
                null,
                SongType::Original->value,
                true,
                1,
                [],
                [],
            ),
        );
    }

    /**
     * @param list<array{personId: string, role: string, orderNo: int}> $persons
     */
    private function assertSong(
        Song $song,
        string $songId,
        string $title,
        string $description,
        ?string $lyricsLink,
        int $typeValue,
        bool $isDisplay,
        int $orderNo,
        array $persons,
    ): bool {
        if (
            $song->songId->value !== $songId
            || $song->title->value !== $title
            || $song->description->value !== $description
            || $song->lyricsLink?->value !== $lyricsLink
            || $song->type->value !== $typeValue
            || $song->isDisplay !== $isDisplay
            || $song->orderNo->value !== $orderNo
            || $song->persons->count() !== count($persons)
        ) {
            return false;
        }

        foreach ($persons as $index => $person) {
            if (
                $song->persons[$index]->personId->value !== $person['personId']
                || $song->persons[$index]->role->value !== $person['role']
                || $song->persons[$index]->orderNo->value !== $person['orderNo']
            ) {
                return false;
            }
        }

        return true;
    }

    private function getInstance(): UpdateUseCase
    {
        $context = $this->privilegedContext();

        return new UpdateUseCase(
            $this->authorizer($context),
            $this->transaction,
            $this->repository,
            $this->service,
            $this->assembler,
            $this->recorder,
        );
    }
}
