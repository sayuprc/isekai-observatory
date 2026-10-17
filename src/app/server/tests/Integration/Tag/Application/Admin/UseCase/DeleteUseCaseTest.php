<?php

declare(strict_types=1);

namespace Tests\Integration\Tag\Application\Admin\UseCase;

use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditTargetType;
use Tag\Application\Admin\UseCase\Delete\DeleteInputData;
use Tag\Application\Admin\UseCase\Delete\DeleteUseCase;
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

        $this->storeTags($this->createTag($uuid, 'タグ', 1));

        $result = $this->getInstance()->handle(new DeleteInputData($uuid));

        $this->assertCount(0, DB::table('tags')->get()->all());

        $this->assertAuditLogCount(1);
        $log = $this->findAuditLog(AuditAction::Delete, AuditTargetType::Tag, $uuid);
        $this->assertSame('タグ', $log['snapshot']['name']);
    }

    private function getInstance(): DeleteUseCase
    {
        $this->privilegedContext();

        return $this->app->make(DeleteUseCase::class);
    }
}
