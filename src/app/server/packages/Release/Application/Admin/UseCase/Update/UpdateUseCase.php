<?php

declare(strict_types=1);

namespace Release\Application\Admin\UseCase\Update;

use AdminUser\Domain\Models\Permission;
use Release\Domain\Models\ReleaseId;
use Release\Domain\Models\ReleaseRepositoryInterface;
use Release\Domain\Services\ReleaseIntegrityService;
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
        private ReleaseRepositoryInterface $repository,
        private ReleaseIntegrityService $service,
        private AuditLogRecorderInterface $recorder,
    ) {
    }

    public function handle(UpdateInputData $inputData): UpdateOutputData
    {
        $this->authorizer->authorize(Permission::WriteRelease);

        $releaseId = new ReleaseId($inputData->releaseId);

        return $this->transaction->scope(function () use ($inputData, $releaseId): UpdateOutputData {
            if (is_null($found = $this->repository->find($releaseId))) {
                throw new ResourceNotFoundException('Release', $releaseId->value);
            }

            // リリースの所属先グループは更新では変更しない
            $release = $this->service->prepareForUpdate(
                $inputData->releaseId,
                $found->releaseGroupId->value,
                $inputData->name,
                $inputData->releasedOn,
                $inputData->description,
                $inputData->color,
                $inputData->isDisplay,
                $inputData->orderNo,
                $inputData->formatValues,
                $inputData->media,
            );

            $release = $this->repository->save($release);

            $this->recorder->record(
                AuditAction::Update,
                AuditTargetType::Release,
                $release->releaseId,
                $release->toArray(),
            );

            return new UpdateOutputData($release);
        });
    }
}
