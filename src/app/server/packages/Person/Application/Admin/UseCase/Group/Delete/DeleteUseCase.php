<?php

declare(strict_types=1);

namespace Person\Application\Admin\UseCase\Group\Delete;

use AdminUser\Domain\Models\Permission;
use Person\Domain\Models\PersonGroupId;
use Person\Domain\Models\PersonGroupRepositoryInterface;
use Person\Domain\Services\PersonGroupUsageCheckerInterface;
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
        private PersonGroupRepositoryInterface $repository,
        private PersonGroupUsageCheckerInterface $usageChecker,
        private AuditLogRecorderInterface $recorder,
    ) {
    }

    public function handle(DeleteInputData $inputData): void
    {
        $this->authorizer->authorize(Permission::WritePerson);

        $personGroupId = new PersonGroupId($inputData->personGroupId);

        $this->transaction->scope(function () use ($personGroupId): void {
            $personGroup = $this->repository->find($personGroupId);

            if ($personGroup === null) {
                return;
            }

            if ($this->usageChecker->isUsed($personGroupId)) {
                throw new BusinessRuleViolationException('この人物グループは楽曲披露に使用されているため削除できません');
            }

            $this->repository->delete($personGroupId);

            $this->recorder->record(
                AuditAction::Delete,
                AuditTargetType::PersonGroup,
                $personGroup->personGroupId,
                $personGroup->toArray(),
            );
        });
    }
}
