<?php

declare(strict_types=1);

namespace Release\Application\Admin\UseCase\Group\Delete;

use AdminUser\Domain\Models\Permission;
use Release\Domain\Models\ReleaseGroupId;
use Release\Domain\Models\ReleaseGroupRepositoryInterface;
use Release\Domain\Models\ReleaseRepositoryInterface;
use Support\Contracts\TransactionInterface;
use Support\Domain\Exceptions\BusinessRuleViolationException;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditLogRecorderInterface;
use Support\UseCase\AuditLog\AuditTargetType;
use Support\UseCase\Authorizer\UseCaseAuthorizer;

readonly class DeleteUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private TransactionInterface $transaction,
        private ReleaseGroupRepositoryInterface $repository,
        private ReleaseRepositoryInterface $releaseRepository,
        private AuditLogRecorderInterface $recorder,
    ) {
    }

    public function handle(DeleteInputData $inputData): void
    {
        $this->authorizer->authorize(Permission::WriteRelease);

        $releaseGroupId = new ReleaseGroupId($inputData->releaseGroupId);

        $this->transaction->scope(function () use ($releaseGroupId): void {
            $releaseGroup = $this->repository->find($releaseGroupId);

            if (is_null($releaseGroup)) {
                return;
            }

            if ($this->releaseRepository->existsByReleaseGroupId($releaseGroupId)) {
                throw new BusinessRuleViolationException('リリースが存在するため削除できません。');
            }

            $this->repository->delete($releaseGroupId);

            $this->recorder->record(
                AuditAction::Delete,
                AuditTargetType::ReleaseGroup,
                $releaseGroup->releaseGroupId,
                $releaseGroup->toArray(),
            );
        });
    }
}
