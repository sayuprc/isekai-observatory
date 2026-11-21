<?php

declare(strict_types=1);

namespace Tag\Application\Admin\UseCase\Create;

use AdminUser\Domain\Models\Permission;
use Support\Contracts\TransactionInterface;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditLogRecorderInterface;
use Support\UseCase\AuditLog\AuditTargetType;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Tag\Domain\Models\TagRepositoryInterface;
use Tag\Domain\Services\TagIntegrityService;

readonly class CreateUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private TransactionInterface $transaction,
        private TagRepositoryInterface $repository,
        private TagIntegrityService $service,
        private AuditLogRecorderInterface $recorder,
    ) {
    }

    public function handle(CreateInputData $inputData): CreateOutputData
    {
        $this->authorizer->authorize(Permission::WriteTag);

        return $this->transaction->scope(function () use ($inputData): CreateOutputData {
            $tag = $this->service->prepareForCreate($inputData->name);

            $this->repository->save($tag);

            $this->recorder->record(
                AuditAction::Create,
                AuditTargetType::Tag,
                $tag->tagId,
                $tag->toArray(),
            );

            return new CreateOutputData($tag);
        });
    }
}
