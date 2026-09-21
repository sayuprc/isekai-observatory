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
use Song\Application\Admin\UseCase\Create\CreateInputData;
use Song\Application\Admin\UseCase\Create\CreateUseCase;
use Song\Domain\Models\Persons\SongPersonRole;
use Song\Domain\Models\Song;
use Song\Domain\Models\SongRepositoryInterface;
use Song\Domain\Models\SongType;
use Song\Domain\Services\SongIntegrityService;
use Support\Contracts\TransactionInterface;
use Support\Domain\Exceptions\BusinessRuleViolationException;
use Support\UseCase\AuditLog\AuditLogRecorderInterface;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class CreateUseCaseTest extends TestCase
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
    public function create(): void
    {
        $songId = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $title = 'テスト楽曲';
        $description = 'テスト楽曲説明';
        $lyricsLink = 'https://example.com/lyrics';
        $typeValue = SongType::Original->value;
        $isDisplay = true;
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

        $this->service->shouldReceive('prepareForCreate')
            ->with($title, $description, $lyricsLink, $typeValue, $isDisplay, [], $persons, [])
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
            new CreateInputData(
                $title,
                $description,
                $lyricsLink,
                $typeValue,
                $isDisplay,
                [],
                $persons,
            ),
        );
    }

    #[Test]
    public function createFailsIfPersonIdNotExists(): void
    {
        $title = '曲名';
        $description = '説明';
        $lyricsLink = null;
        $typeValue = 1;
        $isDisplay = true;
        $persons = [
            ['personId' => 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', 'role' => 1, 'orderNo' => 1],
            ['personId' => 'CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC', 'role' => 2, 'orderNo' => 2],
            ['personId' => 'DDDDDDDD-DDDD-DDDD-DDDD-DDDDDDDDDDDD', 'role' => 3, 'orderNo' => 3],
        ];

        $this->transaction->shouldReceive('scope')
            ->withArgs(static fn (Closure $_) => true)
            ->andReturnUsing(static fn (Closure $arg) => $arg())
            ->once();

        $this->service->shouldReceive('prepareForCreate')
            ->with($title, $description, $lyricsLink, $typeValue, $isDisplay, [], $persons, [])
            ->andThrow(new BusinessRuleViolationException('検証エラー'))
            ->once();

        $this->expectException(BusinessRuleViolationException::class);

        $result = $this->getInstance()->handle(
            new CreateInputData(
                $title,
                $description,
                $lyricsLink,
                $typeValue,
                $isDisplay,
                [],
                $persons,
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

    private function getInstance(): CreateUseCase
    {
        $context = $this->privilegedContext();

        return new CreateUseCase(
            $this->authorizer($context),
            $this->transaction,
            $this->repository,
            $this->service,
            $this->assembler,
            $this->recorder,
        );
    }
}
