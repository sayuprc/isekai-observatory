<?php

declare(strict_types=1);

namespace Tests\Integration\Person\Application\Admin\UseCase\Group;

use Illuminate\Support\Facades\DB;
use Person\Application\Admin\UseCase\Group\Delete\DeleteInputData;
use Person\Application\Admin\UseCase\Group\Delete\DeleteUseCase;
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
    public function deleteRemovesGroupAndMembers(): void
    {
        $personId = $this->generateUuid();
        $personGroupId = $this->generateUuid();
        $this->storePersons($this->createPerson($personId, '人物', 1));
        $this->storePersonGroups($this->createPersonGroup($personGroupId, 'グループ', [$personId]));

        $this->getInstance()->handle(new DeleteInputData($personGroupId));

        $this->assertCount(0, DB::table('person_groups')->get());
        $this->assertCount(0, DB::table('person_group_members')->get());
        $this->assertCount(1, DB::table('persons')->get());
        $this->findAuditLog(AuditAction::Delete, AuditTargetType::PersonGroup, $personGroupId);
    }

    #[Test]
    public function deleteFailsWhenUsedByPerformance(): void
    {
        $personId = $this->generateUuid();
        $personGroupId = $this->generateUuid();
        $songId = $this->generateUuid();
        $this->storePersons($this->createPerson($personId, '人物', 1));
        $this->storePersonGroups($this->createPersonGroup($personGroupId, 'グループ', [$personId]));
        $this->storeSongs($this->createSong($songId, '曲', '', SongType::Original, true, 1));
        $this->storeEvents($this->createEvent(
            $this->generateUuid(),
            performances: [[
                'performanceId' => $this->generateUuid(),
                'songId' => $songId,
                'orderNo' => 1,
                'coVocalists' => [['personId' => $personId, 'creditName' => null, 'personGroupId' => $personGroupId, 'orderNo' => 1]],
            ]],
        ));

        $this->expectException(BusinessRuleViolationException::class);
        $this->expectExceptionMessage('この人物グループは楽曲披露に使用されているため削除できません');

        $this->getInstance()->handle(new DeleteInputData($personGroupId));
    }

    private function getInstance(): DeleteUseCase
    {
        $this->privilegedContext();

        return $this->app->make(DeleteUseCase::class);
    }
}
