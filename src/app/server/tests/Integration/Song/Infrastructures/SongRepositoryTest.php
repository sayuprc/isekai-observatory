<?php

declare(strict_types=1);

namespace Tests\Integration\Song\Infrastructures;

use Person\Domain\Models\PersonId;
use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\SongId;
use Song\Domain\Models\SongType;
use Song\Infrastructures\SongRepository;
use Song\Infrastructures\Tag\SongTagRepository;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class SongRepositoryTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function find(): void
    {
        $repository = $this->getInstance();

        $song = $this->createSong($this->generateUuid(), '曲名', '説明', SongType::Original, true, 1, [], []);

        $repository->save($song);

        $found = $repository->find($song->songId);

        $this->assertNotNull($found);
        $this->assertEquals($song, $found);
    }

    #[Test]
    public function findNotFound(): void
    {
        $found = $this->getInstance()->find(new SongId($this->generateUuid()));

        $this->assertNull($found);
    }

    #[Test]
    public function findWithPersons(): void
    {
        $person = $this->createPerson($this->generateUuid(), '人物', 1);
        $this->storePersons($person);

        $repository = $this->getInstance();

        $song = $this->createSong(
            $this->generateUuid(),
            '曲名',
            '説明',
            SongType::Original,
            true,
            1,
            [],
            [
                ['personId' => $person->personId->value, 'role' => 1, 'orderNo' => 1],
                ['personId' => $person->personId->value, 'role' => 2, 'orderNo' => 2],
                ['personId' => $person->personId->value, 'role' => 3, 'orderNo' => 3],
            ],
        );

        $repository->save($song);

        $found = $repository->find($song->songId);

        $this->assertNotNull($found);
        $this->assertEquals($song, $found);
    }

    #[Test]
    public function isPersonUsed(): void
    {
        $person = $this->createPerson($this->generateUuid(), '人物', 1);
        $this->storePersons($person);

        $repository = $this->getInstance();

        $song = $this->createSong(
            $this->generateUuid(),
            '曲名',
            '説明',
            SongType::Original,
            true,
            1,
            [],
            [['personId' => $person->personId->value, 'role' => 1, 'orderNo' => 1]],
        );

        $repository->save($song);

        $this->assertTrue($repository->isPersonUsed($person->personId));
    }

    #[Test]
    public function isPersonNotUsed(): void
    {
        $this->assertFalse($this->getInstance()->isPersonUsed(new PersonId($this->generateUuid())));
    }

    #[Test]
    public function save(): void
    {
        $repository = $this->getInstance();

        $song = $this->createSong($this->generateUuid(), '曲名', '説明', SongType::Original, true, 1, [], []);

        $repository->save($song);

        $found = $repository->find($song->songId);

        $this->assertNotNull($found);
        $this->assertEquals($song, $found);
    }

    #[Test]
    public function findSortsTagsBySongTagMasterOrder(): void
    {
        $tagRepository = $this->app->make(SongTagRepository::class);
        $tagA = $this->createSongTag($this->generateUuid(), 'タグA', 20);
        $tagB = $this->createSongTag($this->generateUuid(), 'タグB', 10);
        $tagRepository->save($tagA);
        $tagRepository->save($tagB);

        $song = $this->createSong(
            $this->generateUuid(),
            '曲名',
            '説明',
            SongType::Original,
            true,
            1,
            [
                ['songTagId' => $tagA->songTagId->value],
                ['songTagId' => $tagB->songTagId->value],
            ],
            [],
        );

        $this->getInstance()->save($song);
        $found = $this->getInstance()->find($song->songId);

        $this->assertNotNull($found);
        $this->assertSame($tagB->songTagId->value, $found->tags[0]->songTagId->value);
        $this->assertSame($tagA->songTagId->value, $found->tags[1]->songTagId->value);
    }

    #[Test]
    public function saveUpdatesExisting(): void
    {
        $repository = $this->getInstance();

        $song = $this->createSong($this->generateUuid(), '旧タイトル', '旧説明', SongType::Original, true, 1, [], []);
        $repository->save($song);

        $updated = $this->createSong($song->songId->value, '新タイトル', '新説明', SongType::Cover, true, 2, [], []);
        $repository->save($updated);

        $found = $repository->find($song->songId);

        $this->assertNotNull($found);
        $this->assertEquals($updated, $found);
    }

    #[Test]
    public function deleting(): void
    {
        $repository = $this->getInstance();

        $song = $this->createSong($this->generateUuid(), '曲名', '説明', SongType::Original, true, 1, [], []);

        $repository->save($song);
        $repository->delete($song->songId);

        $found = $repository->find($song->songId);

        $this->assertNull($found);
    }

    #[Test]
    public function getMaxOrderNo(): void
    {
        $repository = $this->getInstance();

        $song1 = $this->createSong($this->generateUuid(), '曲1', '説明', SongType::Original, true, 10, [], []);
        $song2 = $this->createSong($this->generateUuid(), '曲2', '説明', SongType::Original, true, 30, [], []);
        $song3 = $this->createSong($this->generateUuid(), '曲3', '説明', SongType::Original, true, 20, [], []);

        $repository->save($song1);
        $repository->save($song2);
        $repository->save($song3);

        $this->assertSame(30, $repository->getMaxOrderNo());
    }

    #[Test]
    public function getMaxOrderNoWhenEmpty(): void
    {
        $this->assertSame(0, $this->getInstance()->getMaxOrderNo());
    }

    private function getInstance(): SongRepository
    {
        return $this->app->make(SongRepository::class);
    }
}
