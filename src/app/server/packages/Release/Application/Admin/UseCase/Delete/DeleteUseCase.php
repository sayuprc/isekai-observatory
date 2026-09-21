<?php

declare(strict_types=1);

namespace Release\Application\Admin\UseCase\Delete;

use AdminUser\Domain\Models\Permission;
use Release\Domain\Models\ReleaseId;
use Release\Domain\Models\ReleaseRepositoryInterface;
use Support\Contracts\TransactionInterface;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditLogRecorderInterface;
use Support\UseCase\AuditLog\AuditTargetType;
use Support\UseCase\Authorizer\UseCaseAuthorizer;

readonly class DeleteUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private TransactionInterface $transaction,
        private ReleaseRepositoryInterface $repository,
        private AuditLogRecorderInterface $recorder,
    ) {
    }

    public function handle(DeleteInputData $inputData): void
    {
        $this->authorizer->authorize(Permission::WriteRelease);

        $releaseId = new ReleaseId($inputData->releaseId);

        $this->transaction->scope(function () use ($releaseId): void {
            $release = $this->repository->find($releaseId);

            if (is_null($release)) {
                return;
            }

            $this->repository->delete($releaseId);

            $this->recorder->record(
                AuditAction::Delete,
                AuditTargetType::Release,
                $release->releaseId,
                $release->toArray(),
            );
        });
    }
}
