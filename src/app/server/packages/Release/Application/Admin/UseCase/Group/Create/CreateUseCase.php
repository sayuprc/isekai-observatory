<?php

declare(strict_types=1);

namespace Release\Application\Admin\UseCase\Group\Create;

use AdminUser\Domain\Models\Permission;
use Release\Domain\Models\ReleaseGroupRepositoryInterface;
use Release\Domain\Services\ReleaseGroupIntegrityService;
use Support\Contracts\TransactionInterface;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditLogRecorderInterface;
use Support\UseCase\AuditLog\AuditTargetType;
use Support\UseCase\Authorizer\UseCaseAuthorizer;

readonly class CreateUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private TransactionInterface $transaction,
        private ReleaseGroupRepositoryInterface $repository,
        private ReleaseGroupIntegrityService $service,
        private AuditLogRecorderInterface $recorder,
    ) {
    }

    public function handle(CreateInputData $inputData): CreateOutputData
    {
        $this->authorizer->authorize(Permission::WriteRelease);

        return $this->transaction->scope(function () use ($inputData): CreateOutputData {
            $releaseGroup = $this->service->prepareForCreate(
                $inputData->title,
                $inputData->typeValue,
                $inputData->description,
                $inputData->isDisplay,
                $inputData->orderNo,
            );

            $releaseGroup = $this->repository->save($releaseGroup);

            $this->recorder->record(
                AuditAction::Create,
                AuditTargetType::ReleaseGroup,
                $releaseGroup->releaseGroupId,
                $releaseGroup->toArray(),
            );

            return new CreateOutputData($releaseGroup);
        });
    }
}
