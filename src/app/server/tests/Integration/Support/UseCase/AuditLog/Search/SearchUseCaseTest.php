<?php

declare(strict_types=1);

namespace Tests\Integration\Support\UseCase\AuditLog\Search;

use AdminUser\Domain\Models\AdminUser;
use AdminUser\Domain\Models\Role;
use Auth\Domain\Models\AuthContext;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Contracts\Uuid\UuidGeneratorInterface;
use Support\Domain\SearchCriteria\PerPage;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditTargetType;
use Support\UseCase\AuditLog\Search\SearchInputData;
use Support\UseCase\AuditLog\Search\SearchUseCase;
use Support\UseCase\Exceptions\PermissionDeniedException;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class SearchUseCaseTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function returnsAllAuditLogsInDescendingOrder(): void
    {
        $actorId = $this->seedActor();

        $this->insertAuditLog($actorId, AuditAction::Create, AuditTargetType::Song, $this->generateUuid(), new DateTimeImmutable('2026-04-01 10:00:00'));
        $this->insertAuditLog($actorId, AuditAction::Update, AuditTargetType::Song, $this->generateUuid(), new DateTimeImmutable('2026-04-02 10:00:00'));
        $this->insertAuditLog($actorId, AuditAction::Delete, AuditTargetType::Person, $this->generateUuid(), new DateTimeImmutable('2026-04-03 10:00:00'));

        $result = $this->getInstance()->handle(new SearchInputData());

        $output = $result;

        $this->assertCount(3, $output->auditLogs);
        $this->assertSame(AuditAction::Delete, $output->auditLogs[0]->action);
        $this->assertSame(AuditAction::Update, $output->auditLogs[1]->action);
        $this->assertSame(AuditAction::Create, $output->auditLogs[2]->action);
        $this->assertSame(1, $output->maxPage);
        $this->assertSame('テストユーザー', $output->auditLogs[0]->adminUserName);
    }

    #[Test]
    public function filtersByDateRange(): void
    {
        $actorId = $this->seedActor();

        $this->insertAuditLog($actorId, AuditAction::Create, AuditTargetType::Song, $this->generateUuid(), new DateTimeImmutable('2026-04-01 00:00:00'));
        $this->insertAuditLog($actorId, AuditAction::Update, AuditTargetType::Song, $this->generateUuid(), new DateTimeImmutable('2026-04-02 12:00:00'));
        $this->insertAuditLog($actorId, AuditAction::Delete, AuditTargetType::Song, $this->generateUuid(), new DateTimeImmutable('2026-04-03 23:59:59'));

        $result = $this->getInstance()->handle(new SearchInputData(
            from: new DateTimeImmutable('2026-04-02 00:00:00'),
            to: new DateTimeImmutable('2026-04-02 23:59:59'),
        ));

        $output = $result;

        $this->assertCount(1, $output->auditLogs);
        $this->assertSame(AuditAction::Update, $output->auditLogs[0]->action);
    }

    #[Test]
    public function filtersByActionAndTargetType(): void
    {
        $actorId = $this->seedActor();

        $this->insertAuditLog($actorId, AuditAction::Create, AuditTargetType::Song, $this->generateUuid(), new DateTimeImmutable('2026-04-01 10:00:00'));
        $this->insertAuditLog($actorId, AuditAction::Update, AuditTargetType::Song, $this->generateUuid(), new DateTimeImmutable('2026-04-02 10:00:00'));
        $this->insertAuditLog($actorId, AuditAction::Update, AuditTargetType::Person, $this->generateUuid(), new DateTimeImmutable('2026-04-03 10:00:00'));

        $result = $this->getInstance()->handle(new SearchInputData(
            action: AuditAction::Update,
            targetType: AuditTargetType::Song,
        ));

        $output = $result;

        $this->assertCount(1, $output->auditLogs);
        $this->assertSame(AuditAction::Update, $output->auditLogs[0]->action);
        $this->assertSame(AuditTargetType::Song, $output->auditLogs[0]->targetType);
    }

    #[Test]
    public function filtersByTargetIdAndAdminUserName(): void
    {
        $actorId = $this->seedActor(name: '監査テストユーザーA');
        $otherActorId = $this->seedActor(email: 'other@example.com', name: '検索テストユーザーB');
        $targetId = $this->generateUuid();

        $this->insertAuditLog($actorId, AuditAction::Create, AuditTargetType::Song, $targetId, new DateTimeImmutable('2026-04-01 10:00:00'));
        $this->insertAuditLog($actorId, AuditAction::Update, AuditTargetType::Song, $this->generateUuid(), new DateTimeImmutable('2026-04-02 10:00:00'));
        $this->insertAuditLog($otherActorId, AuditAction::Update, AuditTargetType::Song, $targetId, new DateTimeImmutable('2026-04-03 10:00:00'));

        $resultByTarget = $this->getInstance()->handle(new SearchInputData(targetId: $targetId));
        $this->assertCount(2, $resultByTarget->auditLogs);

        // 部分一致でヒット('検索' を含むのは '検索テストユーザーB' のみ)
        $resultByActor = $this->getInstance()->handle(new SearchInputData(adminUserName: '検索'));
        $this->assertCount(1, $resultByActor->auditLogs);
        $this->assertSame($targetId, $resultByActor->auditLogs[0]->targetId);
        $this->assertSame('検索テストユーザーB', $resultByActor->auditLogs[0]->adminUserName);

        // 名前完全一致
        $resultExact = $this->getInstance()->handle(new SearchInputData(adminUserName: '監査テストユーザーA'));
        $this->assertCount(2, $resultExact->auditLogs);

        // 中間一致でもヒットする('テストユーザーA' は '監査テストユーザーA' に含まれる)
        $resultSuffix = $this->getInstance()->handle(new SearchInputData(adminUserName: 'テストユーザーA'));
        $this->assertCount(2, $resultSuffix->auditLogs);

        // 不一致
        $resultNoHit = $this->getInstance()->handle(new SearchInputData(adminUserName: '存在しない'));
        $this->assertCount(0, $resultNoHit->auditLogs);
    }

    #[Test]
    public function escapesLikeMetacharactersInAdminUserName(): void
    {
        // メタ文字を含む名前と、含まない名前の両方を seed
        $actorWithPercent = $this->seedActor(name: '100%担当');
        $plainActor = $this->seedActor(email: 'plain@example.com', name: 'プレーン');

        $this->insertAuditLog($actorWithPercent, AuditAction::Create, AuditTargetType::Song, $this->generateUuid(), new DateTimeImmutable('2026-04-01 10:00:00'));
        $this->insertAuditLog($plainActor, AuditAction::Create, AuditTargetType::Song, $this->generateUuid(), new DateTimeImmutable('2026-04-02 10:00:00'));

        // '%' を素のメタ文字として扱うと全件ヒットしてしまう。エスケープされていればリテラル '%' を含む名前のみヒット
        $resultPercent = $this->getInstance()->handle(new SearchInputData(adminUserName: '%'));
        $this->assertCount(1, $resultPercent->auditLogs, '"%" がメタ文字として解釈されないこと');
        $this->assertSame('100%担当', $resultPercent->auditLogs[0]->adminUserName);

        // 名前内の '%' をリテラルとして扱うので '100%' で部分一致がヒット
        $resultLiteral = $this->getInstance()->handle(new SearchInputData(adminUserName: '100%'));
        $this->assertCount(1, $resultLiteral->auditLogs);
        $this->assertSame('100%担当', $resultLiteral->auditLogs[0]->adminUserName);
    }

    #[Test]
    public function paginatesResults(): void
    {
        $actorId = $this->seedActor();

        $totalCount = 26;
        for ($i = 1; $i <= $totalCount; $i++) {
            $this->insertAuditLog(
                $actorId,
                AuditAction::Create,
                AuditTargetType::Song,
                $this->generateUuid(),
                new DateTimeImmutable(sprintf('2026-04-01 10:%02d:00', $i)),
            );
        }

        $page1 = $this->getInstance()->handle(new SearchInputData(page: 1, perPage: PerPage::TwentyFive));
        $this->assertCount(25, $page1->auditLogs);
        $this->assertSame(2, $page1->maxPage);

        $page2 = $this->getInstance()->handle(new SearchInputData(page: 2, perPage: PerPage::TwentyFive));
        $this->assertCount($totalCount - 25, $page2->auditLogs);
        $this->assertSame(2, $page2->maxPage);
    }

    #[Test]
    public function returnsForbiddenWhenLackingPermission(): void
    {
        $context = $this->app->make(AuthContext::class);

        $context->set(AdminUser::reconstruct(
            $this->generateUuid(),
            '一般ユーザー',
            'general@example.com',
            new DateTimeImmutable(),
            Role::General->value,
            [],
        ));

        $useCase = $this->app->make(SearchUseCase::class);

        $this->expectException(PermissionDeniedException::class);

        $result = $useCase->handle(new SearchInputData());
    }

    private function getInstance(): SearchUseCase
    {
        $this->privilegedContext();

        return $this->app->make(SearchUseCase::class);
    }

    private function seedActor(string $email = 'actor@example.com', string $name = 'テストユーザー'): string
    {
        $actorId = $this->generateUuid();
        $actor = $this->createAdminUser($actorId, $email, Role::Privilege, name: $name);
        $this->storeAdminUsers($actor);

        return $actorId;
    }

    private function insertAuditLog(
        string $actorId,
        AuditAction $action,
        AuditTargetType $targetType,
        string $targetId,
        DateTimeImmutable $createdAt,
    ): void {
        $converter = $this->app->make(UuidConverterInterface::class);
        $generator = $this->app->make(UuidGeneratorInterface::class);

        DB::table('audit_logs')->insert([
            'audit_log_id' => $converter->toBin($generator->generate()),
            'admin_user_id' => $converter->toBin($actorId),
            'action' => $action->value,
            'target_type' => $targetType->value,
            'target_id' => $converter->toBin($targetId),
            'snapshot' => json_encode(['placeholder' => true]),
            'created_at' => $createdAt->format('Y-m-d H:i:s'),
        ]);
    }
}
