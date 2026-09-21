<?php

declare(strict_types=1);

namespace Release\Application\Admin\UseCase\Group\Update;

use AdminUser\Domain\Models\Permission;
use Release\Domain\Models\ReleaseGroupId;
use Release\Domain\Models\ReleaseGroupRepositoryInterface;
use Release\Domain\Services\ReleaseGroupIntegrityService;
use Support\Contracts\TransactionInterface;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditLogRecorderInterface;
use Support\UseCase\AuditLog\AuditTargetType;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Support\UseCase\Exceptions\ResourceNotFoundException;

readonly class UpdateUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private TransactionInterface $transaction,
        private ReleaseGroupRepositoryInterface $repository,
        private ReleaseGroupIntegrityService $service,
        private AuditLogRecorderInterface $recorder,
    ) {
    }

    public function handle(UpdateInputData $inputData): UpdateOutputData
    {
        $this->authorizer->authorize(Permission::WriteRelease);

        $releaseGroupId = new ReleaseGroupId($inputData->releaseGroupId);

        return $this->transaction->scope(function () use ($inputData, $releaseGroupId): UpdateOutputData {
            if (is_null($this->repository->find($releaseGroupId))) {
                throw new ResourceNotFoundException('ReleaseGroup', $releaseGroupId->value);
            }

            $releaseGroup = $this->service->prepareForUpdate(
                $inputData->releaseGroupId,
                $inputData->title,
                $inputData->typeValue,
                $inputData->description,
                $inputData->isDisplay,
                $inputData->orderNo,
            );

            $releaseGroup = $this->repository->save($releaseGroup);

            $this->recorder->record(
                AuditAction::Update,
                AuditTargetType::ReleaseGroup,
                $releaseGroup->releaseGroupId,
                $releaseGroup->toArray(),
            );

            return new UpdateOutputData($releaseGroup);
        });
    }
}
