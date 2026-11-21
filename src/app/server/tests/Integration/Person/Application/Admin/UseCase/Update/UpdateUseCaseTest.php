<?php

declare(strict_types=1);

namespace Tests\Integration\Person\Application\Admin\UseCase\Update;

use Person\Application\Admin\UseCase\Update\UpdateInputData;
use Person\Application\Admin\UseCase\Update\UpdateUseCase;
use Person\Domain\Models\PersonRepositoryInterface;
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
        $personId = $this->generateUuid();

        $this->storePersons($this->createPerson($personId, '人物', 10));

        $result = $this->getInstance()->handle(new UpdateInputData($personId, 'テスト人物', 20));

        $persons = $this->app->make(PersonRepositoryInterface::class)->all();
        $this->assertCount(1, $persons);
        $this->assertSame('テスト人物', array_first($persons)->name->value);
        $this->assertSame(20, array_first($persons)->orderNo->value);

        $this->assertAuditLogCount(1);
        $log = $this->findAuditLog(AuditAction::Update, AuditTargetType::Person, $personId);
        $this->assertSame('テスト人物', $log['snapshot']['name']);
        $this->assertSame(20, $log['snapshot']['order_no']);
    }

    #[Test]
    public function updateFailsWhenPersonDoesNotExist(): void
    {
        $personId = $this->generateUuid();

        try {
            $this->getInstance()->handle(new UpdateInputData($personId, 'テスト人物', 20));
            $this->fail('ResourceNotFoundException が発生しませんでした');
        } catch (ResourceNotFoundException) {
        }

        $persons = $this->app->make(PersonRepositoryInterface::class)->all();
        $this->assertCount(0, $persons);
    }

    private function getInstance(): UpdateUseCase
    {
        $this->privilegedContext();

        return $this->app->make(UpdateUseCase::class);
    }
}
