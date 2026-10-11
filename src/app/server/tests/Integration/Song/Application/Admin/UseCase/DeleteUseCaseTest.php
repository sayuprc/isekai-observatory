<?php

declare(strict_types=1);

namespace Tests\Integration\Song\Application\Admin\UseCase;

use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Song\Application\Admin\UseCase\Delete\DeleteInputData;
use Song\Application\Admin\UseCase\Delete\DeleteUseCase;
use Song\Domain\Models\SongType;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditTargetType;
use Tests\Support\Concerns\AssertsAuditLog;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class DeleteUseCaseTest extends DatabaseTestCase
{
    use AssertsAuditLog;
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function canDelete(): void
    {
        $uuid = $this->generateUuid();

        $this->storeSongs(
            $this->createSong($uuid, '', '', SongType::Original, true, 1, [], [], [], []),
        );

        $result = $this->getInstance()->handle(new DeleteInputData($uuid));

        $songs = DB::table('songs')->get();
        $this->assertCount(0, $songs);

        $this->assertAuditLogCount(1);
        $log = $this->findAuditLog(AuditAction::Delete, AuditTargetType::Song, $uuid);
        $this->assertSame($uuid, $log['snapshot']['song_id']);
    }

    private function getInstance(): DeleteUseCase
    {
        $this->privilegedContext();

        return $this->app->make(DeleteUseCase::class);
    }
}
