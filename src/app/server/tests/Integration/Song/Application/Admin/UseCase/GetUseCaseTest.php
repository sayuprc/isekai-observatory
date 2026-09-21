<?php

declare(strict_types=1);

namespace Tests\Integration\Song\Application\Admin\UseCase;

use PHPUnit\Framework\Attributes\Test;
use Song\Application\Admin\UseCase\Get\GetInputData;
use Song\Application\Admin\UseCase\Get\GetUseCase;
use Song\Domain\Models\Persons\SongPersonRole;
use Song\Domain\Models\SongType;
use Support\UseCase\Exceptions\ResourceNotFoundException;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class GetUseCaseTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function getSong(): void
    {
        $lyricist = $this->createPerson($lyricistId = $this->generateUuid(), 'テスト作詞者A', 1);
        $composer = $this->createPerson($composerId = $this->generateUuid(), 'テスト作曲者A', 1);
        $arranger = $this->createPerson($arrangerId = $this->generateUuid(), 'テスト編曲者A', 1);

        $this->storePersons($lyricist, $composer, $arranger);

        $songId = $this->generateUuid();

        $this->storeSongs(
            $this->createSong(
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
            ),
        );

        $result = $this->getInstance()->handle(new GetInputData($songId));

        $response = $result;

        $this->assertSame($songId, $response->song->songId);
        $this->assertSame('テスト楽曲', $response->song->title);
        $this->assertSame('テスト楽曲説明', $response->song->description);
        $this->assertSame(SongType::Original->getName(), $response->song->typeName);
        $this->assertSame(SongType::Original->value, $response->song->typeValue);
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
        $songId = $this->generateUuid();

        $this->expectException(ResourceNotFoundException::class);

        $result = $this->getInstance()->handle(new GetInputData($songId));
    }

    private function getInstance(): GetUseCase
    {
        $this->privilegedContext();

        return $this->app->make(GetUseCase::class);
    }
}
