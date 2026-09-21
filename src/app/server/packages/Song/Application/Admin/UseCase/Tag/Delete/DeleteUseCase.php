<?php

declare(strict_types=1);

namespace Song\Application\Admin\UseCase\Tag\Delete;

use AdminUser\Domain\Models\Permission;
use Song\Domain\Models\Tag\SongTagId;
use Song\Domain\Models\Tag\SongTagRepositoryInterface;
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
        private SongTagRepositoryInterface $repository,
        private AuditLogRecorderInterface $recorder,
    ) {
    }

    public function handle(DeleteInputData $inputData): void
    {
        $this->authorizer->authorize(Permission::WriteSong);

        $songTagId = new SongTagId($inputData->songTagId);

        $this->transaction->scope(function () use ($songTagId): void {
            $tag = $this->repository->find($songTagId);

            if (is_null($tag)) {
                return;
            }

            if ($this->repository->isUsed($songTagId)) {
                throw new BusinessRuleViolationException('この楽曲タグは楽曲に使用されているため削除できません');
            }

            $this->repository->delete($songTagId);

            $this->recorder->record(
                AuditAction::Delete,
                AuditTargetType::SongTag,
                $tag->songTagId,
                $tag->toArray(),
            );
        });
    }
}
