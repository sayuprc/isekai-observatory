<?php

declare(strict_types=1);

namespace Auth\Application\Admin\UseCase\RecoveryCode\Generate;

use AdminUser\Domain\Models\AdminUserRepositoryInterface;
use Auth\Domain\Models\AuthContext;
use Auth\Domain\Models\RecoveryCode\RecoveryCodeRepositoryInterface;
use Auth\Domain\Services\RecoveryCode\RecoveryCodeIssueService;
use Support\Contracts\TransactionInterface;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditLogRecorderInterface;
use Support\UseCase\AuditLog\AuditTargetType;
use Support\UseCase\Exceptions\UnauthenticatedException;

readonly class GenerateRecoveryCodesUseCase
{
    public function __construct(
        private TransactionInterface $transaction,
        private AuthContext $authContext,
        private RecoveryCodeIssueService $issueService,
        private RecoveryCodeRepositoryInterface $repository,
        private AdminUserRepositoryInterface $adminUserRepository,
        private AuditLogRecorderInterface $recorder,
    ) {
    }

    /**
     * @throws UnauthenticatedException
     */
    public function handle(): GenerateRecoveryCodesOutputData
    {
        $adminUser = $this->authContext->get();

        if (is_null($adminUser)) {
            throw new UnauthenticatedException();
        }

        $adminUserId = $adminUser->adminUserId;

        return $this->transaction->scope(function () use ($adminUserId): GenerateRecoveryCodesOutputData {
            // 同一ユーザーの同時発行を直列化する。所有者行を FOR UPDATE でロックし、
            // delete -> insert を他リクエストと交錯させない (両方のコードが残る・デッドロックを防ぐ)
            $this->adminUserRepository->findByIdForUpdate($adminUserId);

            ['codes' => $codes, 'plainCodes' => $plainCodes] = $this->issueService->issue($adminUserId);

            $this->repository->deleteByAdminUserId($adminUserId);
            $this->repository->saveMany($codes);

            $this->recorder->record(
                AuditAction::RecoveryCodeIssue,
                AuditTargetType::AdminUser,
                $adminUserId,
                ['count' => count($codes)],
                $adminUserId,
            );

            return new GenerateRecoveryCodesOutputData($plainCodes);
        });
    }
}
