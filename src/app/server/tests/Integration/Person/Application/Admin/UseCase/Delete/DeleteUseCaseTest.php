<?php

declare(strict_types=1);

namespace Tests\Integration\Person\Application\Admin\UseCase\Delete;

use Illuminate\Support\Facades\DB;
use Person\Application\Admin\UseCase\Delete\DeleteInputData;
use Person\Application\Admin\UseCase\Delete\DeleteUseCase;
use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\SongType;
use Support\Domain\Exceptions\BusinessRuleViolationException;
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

        $this->storePersons($this->createPerson($uuid, '人物', 1));

        $result = $this->getInstance()->handle(new DeleteInputData($uuid));

        $this->assertCount(0, DB::table('persons')->get()->all());

        $this->assertAuditLogCount(1);
        $log = $this->findAuditLog(AuditAction::Delete, AuditTargetType::Person, $uuid);
        $this->assertSame('人物', $log['snapshot']['name']);
    }

    #[Test]
    public function cannotDeleteWhenUsedInSong(): void
    {
        $personId = $this->generateUuid();

        $this->storePersons($this->createPerson($personId, '人物', 1));
        $this->storeSongs($this->createSong(
            $this->generateUuid(),
            '曲名',
            '説明',
            SongType::Original,
            true,
            1,
            [],
            [['personId' => $personId, 'role' => 1, 'orderNo' => 1]],
        ));

        try {
            $this->getInstance()->handle(new DeleteInputData($personId));
            $this->fail('BusinessRuleViolationException が発生しませんでした');
        } catch (BusinessRuleViolationException $e) {
            $this->assertSame('この人物は楽曲に使用されているため削除できません', $e->getMessage());
        }

        $this->assertCount(1, DB::table('persons')->get()->all());
        $this->assertAuditLogCount(0);
    }

    private function getInstance(): DeleteUseCase
    {
        $this->privilegedContext();

        return $this->app->make(DeleteUseCase::class);
    }
}
