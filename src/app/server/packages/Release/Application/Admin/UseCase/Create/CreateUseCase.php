<?php

declare(strict_types=1);

namespace Release\Application\Admin\UseCase\Create;

use AdminUser\Domain\Models\Permission;
use Release\Domain\Models\ReleaseRepositoryInterface;
use Release\Domain\Services\ReleaseIntegrityService;
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
        private ReleaseRepositoryInterface $repository,
        private ReleaseIntegrityService $service,
        private AuditLogRecorderInterface $recorder,
    ) {
    }

    public function handle(CreateInputData $inputData): CreateOutputData
    {
        $this->authorizer->authorize(Permission::WriteRelease);

        return $this->transaction->scope(function () use ($inputData): CreateOutputData {
            $release = $this->service->prepareForCreate(
                $inputData->releaseGroupId,
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
                AuditAction::Create,
                AuditTargetType::Release,
                $release->releaseId,
                $release->toArray(),
            );

            return new CreateOutputData($release);
        });
    }
}
