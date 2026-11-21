<?php

declare(strict_types=1);

namespace Tag\Application\Admin\UseCase\Delete;

use AdminUser\Domain\Models\Permission;
use Support\Contracts\TransactionInterface;
use Support\Domain\Exceptions\BusinessRuleViolationException;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditLogRecorderInterface;
use Support\UseCase\AuditLog\AuditTargetType;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Tag\Domain\Models\TagId;
use Tag\Domain\Models\TagRepositoryInterface;

readonly class DeleteUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private TransactionInterface $transaction,
        private TagRepositoryInterface $repository,
        private AuditLogRecorderInterface $recorder,
    ) {
    }

    public function handle(DeleteInputData $inputData): void
    {
        $this->authorizer->authorize(Permission::WriteTag);

        $tagId = new TagId($inputData->tagId);

        $this->transaction->scope(function () use ($tagId): void {
            $tag = $this->repository->find($tagId);

            if ($tag === null) {
                return;
            }

            if ($this->repository->isUsed($tagId)) {
                throw new BusinessRuleViolationException('このタグはほかの記録に付いているため削除できません');
            }

            $this->repository->delete($tagId);

            $this->recorder->record(
                AuditAction::Delete,
                AuditTargetType::Tag,
                $tag->tagId,
                $tag->toArray(),
            );
        });
    }
}
