<?php

declare(strict_types=1);

namespace Person\Application\Admin\UseCase\Delete;

use AdminUser\Domain\Models\Permission;
use Person\Domain\Models\PersonId;
use Person\Domain\Models\PersonRepositoryInterface;
use Person\Domain\Services\PersonUsageCheckerInterface;
use Support\Contracts\TransactionInterface;
use Support\Domain\Exceptions\BusinessRuleViolationException;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditLogRecorderInterface;
use Support\UseCase\AuditLog\AuditTargetType;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Support\UseCase\Exceptions\ResourceNotFoundException;

readonly class DeleteUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private TransactionInterface $transaction,
        private PersonRepositoryInterface $repository,
        private PersonUsageCheckerInterface $usageChecker,
        private AuditLogRecorderInterface $recorder,
    ) {
    }

    public function handle(DeleteInputData $inputData): void
    {
        $this->authorizer->authorize(Permission::WritePerson);

        $personId = new PersonId($inputData->personId);

        $this->transaction->scope(function () use ($personId): void {
            $person = $this->repository->find($personId);

            if (is_null($person)) {
                throw new ResourceNotFoundException('Person', $personId->value);
            }

            if ($this->usageChecker->isUsed($personId)) {
                throw new BusinessRuleViolationException('この人物は楽曲に使用されているため削除できません');
            }

            $this->repository->delete($personId);

            $this->recorder->record(
                AuditAction::Delete,
                AuditTargetType::Person,
                $person->personId,
                $person->toArray(),
            );
        });
    }
}
