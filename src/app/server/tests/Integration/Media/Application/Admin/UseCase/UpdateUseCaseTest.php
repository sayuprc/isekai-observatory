<?php

declare(strict_types=1);

namespace Tests\Integration\Media\Application\Admin\UseCase;

use Illuminate\Support\Facades\DB;
use Media\Application\Admin\UseCase\Update\UpdateInputData;
use Media\Application\Admin\UseCase\Update\UpdateUseCase;
use Media\Domain\Models\MediaType;
use PHPUnit\Framework\Attributes\Test;
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

        $result = $this->getInstance()->handle(
            new UpdateInputData(
                $mediaId,
                'テストメディア配信アーカイブ',
                'https://example.com/archive',
                '2024-04-02T10:20:30+09:00',
                MediaType::LiveStream->value,
                false,
            ),
        );

        $media = DB::table('media')->first();
        $this->assertNotNull($media);
        $this->assertSame('テストメディア配信アーカイブ', $media->title);
        $this->assertSame('https://example.com/archive', $media->url);
        $this->assertSame('2024-04-02 10:20:30', $media->published_at);
        $this->assertSame(MediaType::LiveStream->value, (int)$media->type);
        $this->assertSame(0, (int)$media->is_display);

        $this->assertAuditLogCount(1);
        $log = $this->findAuditLog(AuditAction::Update, AuditTargetType::Media, $mediaId);
        $this->assertSame('テストメディア配信アーカイブ', $log['snapshot']['title']);
        $this->assertSame(MediaType::LiveStream->value, $log['snapshot']['type']);
        $this->assertFalse($log['snapshot']['is_display']);
    }

    #[Test]
    public function updateFailsWhenMediaDoesNotExist(): void
    {
        $mediaId = $this->generateUuid();

        try {
            $this->getInstance()->handle(
                new UpdateInputData(
                    $mediaId,
                    'テストメディア',
                    'https://example.com/media',
                    '2024-04-02T10:20:30+09:00',
                    MediaType::Mv->value,
                    true,
                ),
            );
            $this->fail('ResourceNotFoundException が発生しませんでした');
        } catch (ResourceNotFoundException) {
        }

        $this->assertDatabaseMissing('media', ['title' => 'テストメディア']);
    }

    private function getInstance(): UpdateUseCase
    {
        $this->privilegedContext();

        return $this->app->make(UpdateUseCase::class);
    }
}
