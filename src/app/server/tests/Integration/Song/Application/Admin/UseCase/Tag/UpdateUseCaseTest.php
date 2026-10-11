<?php

declare(strict_types=1);

namespace Tests\Integration\Song\Application\Admin\UseCase\Tag;

use PHPUnit\Framework\Attributes\Test;
use Song\Application\Admin\UseCase\Tag\Update\UpdateInputData;
use Song\Application\Admin\UseCase\Tag\Update\UpdateUseCase;
use Song\Domain\Models\Tag\SongTagRepositoryInterface;
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
        $uuid = $this->generateUuid();

        $this->storeSongTags($this->createSongTag($uuid, '旧タグ', 1));

        $result = $this->getInstance()->handle(new UpdateInputData($uuid, 'テストタグA', 2));

        $tags = $this->app->make(SongTagRepositoryInterface::class)->all();
        $this->assertCount(1, $tags);
        $this->assertSame('テストタグA', array_first($tags)->name->value);
        $this->assertSame(2, array_first($tags)->orderNo->value);

        $this->assertAuditLogCount(1);
        $log = $this->findAuditLog(AuditAction::Update, AuditTargetType::SongTag, $uuid);
        $this->assertSame('テストタグA', $log['snapshot']['name']);
    }

    #[Test]
    public function updateFailsWhenSongTagDoesNotExist(): void
    {
        $uuid = $this->generateUuid();

        $this->expectException(ResourceNotFoundException::class);

        $result = $this->getInstance()->handle(new UpdateInputData($uuid, 'テストタグA', 2));
    }

    private function getInstance(): UpdateUseCase
    {
        $this->privilegedContext();

        return $this->app->make(UpdateUseCase::class);
    }
}
