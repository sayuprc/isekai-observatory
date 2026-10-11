<?php

declare(strict_types=1);

namespace Tests\Integration\Person\Application\Admin\UseCase\Group;

use Person\Application\Admin\UseCase\Group\Update\UpdateInputData;
use Person\Application\Admin\UseCase\Group\Update\UpdateUseCase;
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
    public function updateReplacesNameAndMembers(): void
    {
        $person1 = $this->generateUuid();
        $person2 = $this->generateUuid();
        $personGroupId = $this->generateUuid();
        $this->storePersons($this->createPerson($person1, '人物1', 1), $this->createPerson($person2, '人物2', 2));
        $this->storePersonGroups($this->createPersonGroup($personGroupId, '旧グループ', [$person1, $person2]));

        $result = $this->getInstance()->handle(new UpdateInputData($personGroupId, '新グループ', [
            ['personId' => $person2, 'orderNo' => 1],
        ]));

        $this->assertSame('新グループ', $result->personGroup->name);
        $this->assertSame([$person2], array_column($result->personGroup->members, 'personId'));
        $this->findAuditLog(AuditAction::Update, AuditTargetType::PersonGroup, $personGroupId);
    }

    #[Test]
    public function updateFailsWhenNotFound(): void
    {
        $this->expectException(ResourceNotFoundException::class);

        $this->getInstance()->handle(new UpdateInputData($this->generateUuid(), 'グループ', []));
    }

    private function getInstance(): UpdateUseCase
    {
        $this->privilegedContext();

        return $this->app->make(UpdateUseCase::class);
    }
}
