<?php

declare(strict_types=1);

namespace Tests\Unit\Song\Application\Admin\Assemble;

use Media\Domain\Models\MediaRepositoryInterface;
use Mockery;
use Mockery\MockInterface;
use Override;
use Person\Domain\Models\Person;
use Person\Domain\Models\PersonId;
use Person\Domain\Models\PersonName;
use Person\Domain\Models\PersonRepositoryInterface;
use PHPUnit\Framework\Attributes\Test;
use Song\Application\Admin\Assemble\SongAssembler;
use Song\Domain\Models\Persons\SongPersonRole;
use Song\Domain\Models\SongType;
use Song\Domain\Models\Tag\SongTagRepositoryInterface;
use Support\Domain\ValueObjects\OrderNo;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class SongAssemblerTest extends TestCase
{
    use EntityFactory;

    private MockInterface&PersonRepositoryInterface $personRepository;

    private MockInterface&SongTagRepositoryInterface $songTagRepository;

    private MediaRepositoryInterface&MockInterface $mediaRepository;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->personRepository = Mockery::mock(PersonRepositoryInterface::class);
        $this->songTagRepository = Mockery::mock(SongTagRepositoryInterface::class);
        $this->mediaRepository = Mockery::mock(MediaRepositoryInterface::class);
    }

    #[Test]
    public function canAssemble(): void
    {
        $uuid = $this->generateUuid();
        $title = 'テスト楽曲';
        $description = 'テスト楽曲説明';
        $type = SongType::Original;
        $orderNo = 1;

        $song = $this->createSong(
            $uuid,
            $title,
            $description,
            $type,
            true,
            $orderNo,
            [],
            [
                ['personId' => $lyricistId = $this->generateUuid(), 'role' => 1, 'orderNo' => 1],
                ['personId' => $composerId = $this->generateUuid(), 'role' => 2, 'orderNo' => 2],
                ['personId' => $arrangerId = $this->generateUuid(), 'role' => 3, 'orderNo' => 3],
            ],
        );

        $this->personRepository->shouldReceive('findByIds')
            ->withArgs(static function (PersonId ...$ids) use ($lyricistId, $composerId, $arrangerId): bool {
                $idValues = array_map(static fn (PersonId $id): string => $id->value, $ids);
                sort($idValues);
                $expectedIds = [$lyricistId, $composerId, $arrangerId];
                sort($expectedIds);

                return $idValues === $expectedIds;
            })
            ->andReturn([
                new Person(new PersonId($lyricistId), new PersonName('テスト作詞者'), new OrderNo(1)),
                new Person(new PersonId($composerId), new PersonName('テスト作曲者'), new OrderNo(1)),
                new Person(new PersonId($arrangerId), new PersonName('テスト編曲者'), new OrderNo(1)),
            ])
            ->once();

        $assembled = $this->getInstance()->assemble($song);

        $this->assertSame($uuid, $assembled->songId);
        $this->assertSame($title, $assembled->title);
        $this->assertSame($description, $assembled->description);
        $this->assertSame($type->getName(), $assembled->typeName);
        $this->assertSame($type->value, $assembled->typeValue);
        $this->assertSame($orderNo, $assembled->orderNo);
        $this->assertCount(3, $assembled->persons);
        $this->assertSame($lyricistId, $assembled->persons[0]->personId);
        $this->assertSame('テスト作詞者', $assembled->persons[0]->name);
        $this->assertSame(SongPersonRole::Lyricist, $assembled->persons[0]->role);
        $this->assertSame($composerId, $assembled->persons[1]->personId);
        $this->assertSame(SongPersonRole::Composer, $assembled->persons[1]->role);
        $this->assertSame($arrangerId, $assembled->persons[2]->personId);
        $this->assertSame(SongPersonRole::Arranger, $assembled->persons[2]->role);
    }

    #[Test]
    public function canAssembleWithoutPersons(): void
    {
        $uuid = $this->generateUuid();
        $title = 'インストゥルメンタル';
        $description = '人物無しの楽曲';
        $type = SongType::Original;
        $orderNo = 1;

        $song = $this->createSong(
            $uuid,
            $title,
            $description,
            $type,
            true,
            $orderNo,
            [],
            [],
        );

        $this->personRepository->shouldReceive('findByIds')->never();

        $assembled = $this->getInstance()->assemble($song);

        $this->assertSame($uuid, $assembled->songId);
        $this->assertSame($title, $assembled->title);
        $this->assertSame($description, $assembled->description);
        $this->assertSame($type->getName(), $assembled->typeName);
        $this->assertSame($type->value, $assembled->typeValue);
        $this->assertSame($orderNo, $assembled->orderNo);
        $this->assertCount(0, $assembled->persons);
    }

    private function getInstance(): SongAssembler
    {
        return new SongAssembler($this->personRepository, $this->songTagRepository, $this->mediaRepository);
    }
}
