<?php

declare(strict_types=1);

namespace Tests\Integration\Song\Application\Admin\UseCase;

use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Song\Application\Admin\UseCase\Create\CreateInputData;
use Song\Application\Admin\UseCase\Create\CreateUseCase;
use Song\Domain\Models\SongType;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditTargetType;
use Tests\Support\Concerns\AssertsAuditLog;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class CreateUseCaseTest extends DatabaseTestCase
{
    use AssertsAuditLog;
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function create(): void
    {
        $person1 = $this->createPerson($this->generateUuid(), 'テスト作詞者', 1);
        $person2 = $this->createPerson($this->generateUuid(), 'テスト作曲者', 1);
        $person3 = $this->createPerson($this->generateUuid(), 'テスト編曲者', 1);

        $this->storePersons($person1, $person2, $person3);

        $result = $this->getInstance()->handle(
            new CreateInputData(
                'テスト楽曲',
                'テスト楽曲説明',
                'https://example.com/lyrics',
                SongType::Original->value,
                true,
                [],
                [
                    ['personId' => $person1->personId->value, 'role' => 1, 'orderNo' => 1],
                    ['personId' => $person2->personId->value, 'role' => 2, 'orderNo' => 2],
                    ['personId' => $person3->personId->value, 'role' => 3, 'orderNo' => 3],
                ],
            ),
        );

        $songs = DB::table('songs')->get()->all();
        $this->assertCount(1, $songs);
        $song = array_first($songs);
        $this->assertSame('テスト楽曲', $song->title);
        $this->assertSame('テスト楽曲説明', $song->description);
        $this->assertSame('https://example.com/lyrics', $song->lyrics_link);
        $this->assertSame(SongType::Original->value, (int)$song->type);
        $this->assertSame(1, (int)$song->is_display);
        $this->assertSame(10, (int)$song->order_no);

        $persons = DB::table('song_persons')
            ->where('song_id', $song->song_id)
            ->orderBy('order_no')
            ->get()
            ->all();
        $this->assertCount(3, $persons);
        $this->assertSame($person1->personId->value, $this->toUuid($persons[0]->person_id));
        $this->assertSame(1, (int)$persons[0]->role);
        $this->assertSame(1, (int)$persons[0]->order_no);
        $this->assertSame($person2->personId->value, $this->toUuid($persons[1]->person_id));
        $this->assertSame(2, (int)$persons[1]->role);
        $this->assertSame(2, (int)$persons[1]->order_no);
        $this->assertSame($person3->personId->value, $this->toUuid($persons[2]->person_id));
        $this->assertSame(3, (int)$persons[2]->role);
        $this->assertSame(3, (int)$persons[2]->order_no);

        $songId = $this->toUuid($song->song_id);
        $this->assertAuditLogCount(1);
        $log = $this->findAuditLog(AuditAction::Create, AuditTargetType::Song, $songId);
        $this->assertSame('テスト楽曲', $log['snapshot']['title']);
        $this->assertCount(3, $log['snapshot']['persons']);
    }

    private function getInstance(): CreateUseCase
    {
        $this->privilegedContext();

        return $this->app->make(CreateUseCase::class);
    }
}
