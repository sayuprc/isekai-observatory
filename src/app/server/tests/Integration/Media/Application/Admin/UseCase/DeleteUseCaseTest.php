<?php

declare(strict_types=1);

namespace Tests\Integration\Media\Application\Admin\UseCase;

use Illuminate\Support\Facades\DB;
use Media\Application\Admin\UseCase\Delete\DeleteInputData;
use Media\Application\Admin\UseCase\Delete\DeleteUseCase;
use Media\Domain\Models\MediaType;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Song\Domain\Models\SongType;
use Support\Domain\Exceptions\BusinessRuleViolationException;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditLogRecorderInterface;
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
        $mediaId = $this->generateUuid();

        $this->storeMedia(
            $this->createMedia(
                $mediaId,
                'テストメディアMV',
                'https://example.com/media',
                MediaType::Mv,
                true,
            ),
        );

        $result = $this->getInstance()->handle(new DeleteInputData($mediaId));

        $this->assertCount(0, DB::table('media')->get()->all());

        $this->assertAuditLogCount(1);
        $log = $this->findAuditLog(AuditAction::Delete, AuditTargetType::Media, $mediaId);
        $this->assertSame('テストメディアMV', $log['snapshot']['title']);
    }

    #[Test]
    public function cannotDeleteWhenUsedInSong(): void
    {
        $mediaId = $this->generateUuid();

        $this->storeMedia(
            $this->createMedia(
                $mediaId,
                'テストメディアMV',
                'https://example.com/media',
                MediaType::Mv,
                true,
            ),
        );
        $this->storeSongs($this->createSong(
            $this->generateUuid(),
            '曲名',
            '説明',
            null,
            SongType::Original,
            true,
            1,
            [],
            [],
            [],
            [
                ['mediaId' => $mediaId, 'orderNo' => 1],
            ],
        ));

        try {
            $this->getInstance()->handle(new DeleteInputData($mediaId));
            $this->fail('BusinessRuleViolationException が発生しませんでした');
        } catch (BusinessRuleViolationException $e) {
            $this->assertSame('このメディアは楽曲に使用されているため削除できません', $e->getMessage());
        }

        $this->assertCount(1, DB::table('media')->get()->all());
        $this->assertAuditLogCount(0);
    }

    #[Test]
    public function rollsBackDeleteWhenAuditLogRecorderThrows(): void
    {
        $mediaId = $this->generateUuid();

        $this->storeMedia(
            $this->createMedia(
                $mediaId,
                'ロールバック対象',
                'https://example.com/rollback',
                MediaType::Mv,
                true,
            ),
        );

        $this->privilegedContext();

        $recorder = Mockery::mock(AuditLogRecorderInterface::class);
        $recorder->shouldReceive('record')->andThrow(new RuntimeException('audit log failure'));
        $this->app->instance(AuditLogRecorderInterface::class, $recorder);

        try {
            $this->app->make(DeleteUseCase::class)->handle(new DeleteInputData($mediaId));
            $this->fail('RuntimeException が送出されるはず');
        } catch (RuntimeException $e) {
            $this->assertSame('audit log failure', $e->getMessage());
        }

        $this->assertCount(1, DB::table('media')->where('title', 'ロールバック対象')->get());
        $this->assertAuditLogCount(0);
    }

    private function getInstance(): DeleteUseCase
    {
        $this->privilegedContext();

        return $this->app->make(DeleteUseCase::class);
    }
}
