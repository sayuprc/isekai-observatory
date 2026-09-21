<?php

declare(strict_types=1);

namespace Tests\Integration\Media\Application\Admin\UseCase;

use Illuminate\Support\Facades\DB;
use Media\Application\Admin\UseCase\Create\CreateInputData;
use Media\Application\Admin\UseCase\Create\CreateUseCase;
use Media\Domain\Models\MediaType;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditLogRecorderInterface;
use Support\UseCase\AuditLog\AuditTargetType;
use Tests\Support\Concerns\AssertsAuditLog;
use Tests\Support\DatabaseTestCase;

class CreateUseCaseTest extends DatabaseTestCase
{
    use AssertsAuditLog;

    #[Test]
    public function create(): void
    {
        $result = $this->getInstance()->handle(
            new CreateInputData(
                'テストメディアMV',
                'https://example.com/media',
                '2024-03-01T12:34:56+09:00',
                MediaType::Mv->value,
                true,
            ),
        );

        $media = DB::table('media')->first();
        $this->assertNotNull($media);
        $this->assertSame('テストメディアMV', $media->title);
        $this->assertSame('https://example.com/media', $media->url);
        $this->assertSame('2024-03-01 12:34:56', $media->published_at);

        $mediaId = $this->toUuid($media->media_id);
        $this->assertAuditLogCount(1);
        $log = $this->findAuditLog(AuditAction::Create, AuditTargetType::Media, $mediaId);
        $this->assertSame('テストメディアMV', $log['snapshot']['title']);
        $this->assertSame(MediaType::Mv->value, $log['snapshot']['type']);
    }

    #[Test]
    public function rollsBackBusinessDataWhenAuditLogRecorderThrows(): void
    {
        $this->privilegedContext();

        $recorder = Mockery::mock(AuditLogRecorderInterface::class);
        $recorder->shouldReceive('record')->andThrow(new RuntimeException('audit log failure'));
        $this->app->instance(AuditLogRecorderInterface::class, $recorder);

        try {
            $this->app->make(CreateUseCase::class)->handle(
                new CreateInputData(
                    'ロールバック対象',
                    'https://example.com/rollback',
                    '2024-03-01T12:34:56+09:00',
                    MediaType::Mv->value,
                    true,
                ),
            );
            $this->fail('RuntimeException が送出されるはず');
        } catch (RuntimeException $e) {
            $this->assertSame('audit log failure', $e->getMessage());
        }

        $this->assertCount(0, DB::table('media')->where('title', 'ロールバック対象')->get());
        $this->assertAuditLogCount(0);
    }

    private function getInstance(): CreateUseCase
    {
        $this->privilegedContext();

        return $this->app->make(CreateUseCase::class);
    }
}
