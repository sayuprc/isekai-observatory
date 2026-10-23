<?php

declare(strict_types=1);

namespace Tag\Application\Admin\UseCase\Update;

use AdminUser\Domain\Models\Permission;
use Support\Contracts\TransactionInterface;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditLogRecorderInterface;
use Support\UseCase\AuditLog\AuditTargetType;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Support\UseCase\Exceptions\ResourceNotFoundException;
use Tag\Domain\Models\TagId;
use Tag\Domain\Models\TagRepositoryInterface;
use Tag\Domain\Services\TagIntegrityService;

readonly class UpdateUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private TransactionInterface $transaction,
        private TagRepositoryInterface $repository,
        private TagIntegrityService $service,
        private AuditLogRecorderInterface $recorder,
    ) {
    }

    public function handle(UpdateInputData $inputData): UpdateOutputData
    {
        $this->authorizer->authorize(Permission::WriteTag);

        $tagId = new TagId($inputData->tagId);

        return $this->transaction->scope(function () use ($inputData, $tagId): UpdateOutputData {
            if ($this->repository->find($tagId) === null) {
                throw new ResourceNotFoundException('Tag', $tagId->value);
            }

            $tag = $this->service->prepareForUpdate($inputData->tagId, $inputData->name, $inputData->orderNo);

            $this->repository->save($tag);

            $this->recorder->record(
                AuditAction::Update,
                AuditTargetType::Tag,
                $tag->tagId,
                $tag->toArray(),
            );

            return new UpdateOutputData($tag);
        });
    }
}
