<?php

declare(strict_types=1);

namespace Tests\Integration\Release\Application\Admin\UseCase\Delete;

use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Release\Application\Admin\UseCase\Delete\DeleteInputData;
use Release\Application\Admin\UseCase\Delete\DeleteUseCase;
use Release\Domain\Models\ReleaseFormat;
use Release\Domain\Models\ReleaseGroupType;
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
        $releaseGroupId = $this->generateUuid();
        $releaseId = $this->generateUuid();

        $this->storeReleaseGroups(
            $this->createReleaseGroup($releaseGroupId, '観測された春', ReleaseGroupType::Album, true),
        );
        $this->storeReleases(
            $this->createRelease($releaseId, $releaseGroupId, '削除対象', true),
        );

        $result = $this->getInstance()->handle(new DeleteInputData($releaseId));

        $this->assertCount(0, DB::table('releases')->get()->all());

        $this->assertAuditLogCount(1);
        $log = $this->findAuditLog(AuditAction::Delete, AuditTargetType::Release, $releaseId);
        $snapshot = $log['snapshot'];
        $this->assertIsArray($snapshot);
        $this->assertSame('削除対象', $snapshot['name'] ?? null);
    }

    #[Test]
    public function canDeleteReleaseWithMediaAndTracks(): void
    {
        $songId = $this->generateUuid();
        $releaseGroupId = $this->generateUuid();
        $releaseId = $this->generateUuid();

        $this->storeSongs(
            $this->createSong($songId, 'テスト楽曲1', '説明', SongType::Original, true, 1),
        );
        $this->storeReleaseGroups(
            $this->createReleaseGroup($releaseGroupId, '観測された春', ReleaseGroupType::Album, true),
        );
        $this->storeReleases(
            $this->createRelease(
                $releaseId,
                $releaseGroupId,
                '削除対象',
                true,
                formats: [ReleaseFormat::Cd->value],
                media: [
                    [
                        'position' => 1,
                        'name' => null,
                        'tracks' => [['songId' => $songId, 'title' => null, 'trackNo' => 1]],
                    ],
                ],
            ),
        );

        $result = $this->getInstance()->handle(new DeleteInputData($releaseId));

        $this->assertCount(0, DB::table('releases')->get()->all());
        $this->assertCount(0, DB::table('release_media')->get()->all());
        $this->assertCount(0, DB::table('release_tracks')->get()->all());
        // グループ自体は残る
        $this->assertCount(1, DB::table('release_groups')->get()->all());
    }

    #[Test]
    public function canDeleteEvenIfTargetDoesNotExist(): void
    {
        $result = $this->getInstance()->handle(new DeleteInputData($this->generateUuid()));

        $this->assertAuditLogCount(0);
    }

    private function getInstance(): DeleteUseCase
    {
        $this->privilegedContext();

        return $this->app->make(DeleteUseCase::class);
    }
}
