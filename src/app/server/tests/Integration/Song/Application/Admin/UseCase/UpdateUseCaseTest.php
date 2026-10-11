<?php

declare(strict_types=1);

namespace Tests\Integration\Song\Application\Admin\UseCase;

use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Song\Application\Admin\UseCase\Update\UpdateInputData;
use Song\Application\Admin\UseCase\Update\UpdateUseCase;
use Song\Domain\Models\SongType;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditTargetType;
use Support\UseCase\Exceptions\ResourceNotFoundException;
use Tests\Support\Concerns\AssertsAuditLog;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class UpdateUseCaseTest extends DatabaseTestCase
{
    use AssertsAuditLog;
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function canUpdate(): void
    {
        $person1 = $this->createPerson($this->generateUuid(), 'テスト作詞者', 1);
        $person2 = $this->createPerson($this->generateUuid(), 'テスト作曲者', 1);
        $person3 = $this->createPerson($this->generateUuid(), 'テスト編曲者', 1);

        $this->storePersons($person1, $person2, $person3);

        $songId = $this->generateUuid();

        $this->storeSongs(
            $this->createSong(
                $songId,
                '曲名',
                '説明',
                SongType::Original,
                true,
                1,
                [],
                [
                    ['personId' => $person1->personId->value, 'role' => 1, 'orderNo' => 1],
                    ['personId' => $person2->personId->value, 'role' => 2, 'orderNo' => 2],
                    ['personId' => $person3->personId->value, 'role' => 3, 'orderNo' => 3],
                ],
            ),
        );

        $result = $this->getInstance()->handle(
            new UpdateInputData(
                $songId,
                'テスト楽曲',
                'テスト楽曲説明',
                'https://example.com/lyrics',
                SongType::Cover->value,
                false,
                2,
                [],
                [
                    ['personId' => $person2->personId->value, 'role' => 2, 'orderNo' => 1],
                    ['personId' => $person3->personId->value, 'role' => 3, 'orderNo' => 2],
                ],
            ),
        );

        $songs = DB::table('songs')->get()->all();
        $this->assertCount(1, $songs);
        $song = array_first($songs);
        $this->assertSame('テスト楽曲', $song->title);
        $this->assertSame('テスト楽曲説明', $song->description);
        $this->assertSame('https://example.com/lyrics', $song->lyrics_link);
        $this->assertSame(SongType::Cover->value, (int)$song->type);
        $this->assertSame(0, (int)$song->is_display);
        $this->assertSame(2, (int)$song->order_no);

        $persons = DB::table('song_persons')
            ->where('song_id', $song->song_id)
            ->orderBy('order_no')
            ->get()
            ->all();
        $this->assertCount(2, $persons);
        $this->assertSame($person2->personId->value, $this->toUuid($persons[0]->person_id));
        $this->assertSame(2, (int)$persons[0]->role);
        $this->assertSame($person3->personId->value, $this->toUuid($persons[1]->person_id));
        $this->assertSame(3, (int)$persons[1]->role);

        $this->assertAuditLogCount(1);
        $log = $this->findAuditLog(AuditAction::Update, AuditTargetType::Song, $songId);
        $this->assertSame('テスト楽曲', $log['snapshot']['title']);
        $this->assertCount(2, $log['snapshot']['persons']);
    }

    #[Test]
    public function updateFailsWhenSongDoesNotExist(): void
    {
        $songId = $this->generateUuid();

        try {
            $this->getInstance()->handle(
                new UpdateInputData(
                    $songId,
                    'テスト楽曲',
                    'テスト楽曲説明',
                    null,
                    SongType::Original->value,
                    true,
                    1,
                    [],
                    [],
                ),
            );
            $this->fail('ResourceNotFoundException が発生しませんでした');
        } catch (ResourceNotFoundException) {
        }

        $this->assertDatabaseMissing('songs', ['title' => 'テスト楽曲']);
    }

    private function getInstance(): UpdateUseCase
    {
        $this->privilegedContext();

        return $this->app->make(UpdateUseCase::class);
    }
}
