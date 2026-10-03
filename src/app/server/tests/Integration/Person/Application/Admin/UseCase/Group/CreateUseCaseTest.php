<?php

declare(strict_types=1);

namespace Tests\Integration\Person\Application\Admin\UseCase\Group;

use Illuminate\Support\Facades\DB;
use Person\Application\Admin\UseCase\Group\Create\CreateInputData;
use Person\Application\Admin\UseCase\Group\Create\CreateUseCase;
use PHPUnit\Framework\Attributes\Test;
use Support\Domain\Exceptions\BusinessRuleViolationException;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditTargetType;
use Tests\Support\Concerns\AssertsAuditLog;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class CreateUseCaseTest extends DatabaseTestCase
{
    use AssertsAuditLog;
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function createWithMembers(): void
    {
        $person1 = $this->generateUuid();
        $person2 = $this->generateUuid();
        $this->storePersons($this->createPerson($person1, '人物1', 1), $this->createPerson($person2, '人物2', 2));

        $result = $this->getInstance()->handle(new CreateInputData('グループ', [
            ['personId' => $person2, 'orderNo' => 1],
            ['personId' => $person1, 'orderNo' => 2],
        ]));

        $this->assertSame('グループ', $result->personGroup->name);
        $this->assertSame([$person2, $person1], array_column($result->personGroup->members, 'personId'));
        $this->assertSame(['人物2', '人物1'], array_column($result->personGroup->members, 'name'));
        $this->assertCount(2, DB::table('person_group_members')->get());

        $log = $this->findAuditLog(AuditAction::Create, AuditTargetType::PersonGroup, $result->personGroup->personGroupId);
        $this->assertSame('グループ', $log['snapshot']['name']);
        $this->assertCount(2, $log['snapshot']['members']);
    }

    #[Test]
    public function createFailsWhenNameAlreadyExists(): void
    {
        $this->storePersonGroups($this->createPersonGroup($this->generateUuid(), 'グループ'));

        $this->expectException(BusinessRuleViolationException::class);
        $this->expectExceptionMessage('すでに使われている名前です "グループ"');

        $this->getInstance()->handle(new CreateInputData('グループ', []));
    }

    #[Test]
    public function createFailsWhenMemberDoesNotExist(): void
    {
        $this->expectException(BusinessRuleViolationException::class);
        $this->expectExceptionMessage('指定された人物の一部が存在しません');

        $this->getInstance()->handle(new CreateInputData('グループ', [['personId' => $this->generateUuid(), 'orderNo' => 1]]));
    }

    private function getInstance(): CreateUseCase
    {
        $this->privilegedContext();

        return $this->app->make(CreateUseCase::class);
    }
}
