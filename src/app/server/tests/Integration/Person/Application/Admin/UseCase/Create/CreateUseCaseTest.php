<?php

declare(strict_types=1);

namespace Tests\Integration\Person\Application\Admin\UseCase\Create;

use Illuminate\Support\Facades\DB;
use Mockery;
use Person\Application\Admin\UseCase\Create\CreateInputData;
use Person\Application\Admin\UseCase\Create\CreateUseCase;
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
        $result = $this->getInstance()->handle(new CreateInputData('テスト人物'));

        $persons = DB::table('persons')->get();
        $this->assertCount(1, $persons);
        $this->assertSame('テスト人物', $persons->first()->name);

        $personId = $this->toUuid($persons->first()->person_id);

        $this->assertAuditLogCount(1);
        $log = $this->findAuditLog(AuditAction::Create, AuditTargetType::Person, $personId);
        $this->assertSame('テスト人物', $log['snapshot']['name']);
        $this->assertSame($personId, $log['target_id']);
    }

    #[Test]
    public function rollsBackBusinessDataWhenAuditLogRecorderThrows(): void
    {
        $this->privilegedContext();

        $recorder = Mockery::mock(AuditLogRecorderInterface::class);
        $recorder->shouldReceive('record')->andThrow(new RuntimeException('audit log failure'));
        $this->app->instance(AuditLogRecorderInterface::class, $recorder);

        try {
            $this->app->make(CreateUseCase::class)->handle(new CreateInputData('ロールバック対象'));
            $this->fail('RuntimeException が送出されるはず');
        } catch (RuntimeException $e) {
            $this->assertSame('audit log failure', $e->getMessage());
        }

        $this->assertCount(0, DB::table('persons')->where('name', 'ロールバック対象')->get());
        $this->assertAuditLogCount(0);
    }

    private function getInstance(): CreateUseCase
    {
        $this->privilegedContext();

        return $this->app->make(CreateUseCase::class);
    }
}
