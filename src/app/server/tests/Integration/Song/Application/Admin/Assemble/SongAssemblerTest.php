<?php

declare(strict_types=1);

namespace Tests\Integration\Song\Application\Admin\Assemble;

use PHPUnit\Framework\Attributes\Test;
use Song\Application\Admin\Assemble\SongAssembler;
use Song\Domain\Models\Persons\SongPersonRole;
use Song\Domain\Models\SongType;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class SongAssemblerTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function canAssemble(): void
    {
        $uuid = $this->generateUuid();
        $title = 'テスト楽曲';
        $description = 'テスト楽曲説明';
        $type = SongType::Original;
        $orderNo = 1;

        $lyricistId = $this->generateUuid();
        $composerId = $this->generateUuid();
        $arrangerId = $this->generateUuid();

        $song = $this->createSong(
            $uuid,
            $title,
            $description,
            $type,
            true,
            $orderNo,
            [],
            [
                ['personId' => $lyricistId, 'role' => 1, 'orderNo' => 1],
                ['personId' => $composerId, 'role' => 2, 'orderNo' => 2],
                ['personId' => $arrangerId, 'role' => 3, 'orderNo' => 3],
            ],
        );

        $this->storePersons(
            $this->createPerson($lyricistId, 'テスト作詞者', 1),
            $this->createPerson($composerId, 'テスト作曲者', 1),
            $this->createPerson($arrangerId, 'テスト編曲者', 1),
        );

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

    private function getInstance(): SongAssembler
    {
        return $this->app->make(SongAssembler::class);
    }
}
