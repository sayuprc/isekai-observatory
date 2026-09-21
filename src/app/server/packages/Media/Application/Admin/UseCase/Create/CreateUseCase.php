<?php

declare(strict_types=1);

namespace Media\Application\Admin\UseCase\Create;

use AdminUser\Domain\Models\Permission;
use Media\Domain\Models\MediaRepositoryInterface;
use Media\Domain\Services\MediaIntegrityService;
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
        private MediaRepositoryInterface $repository,
        private MediaIntegrityService $service,
        private AuditLogRecorderInterface $recorder,
    ) {
    }

    public function handle(CreateInputData $inputData): CreateOutputData
    {
        $this->authorizer->authorize(Permission::WriteMedia);

        return $this->transaction->scope(function () use ($inputData): CreateOutputData {
            $media = $this->service->prepareForCreate(
                $inputData->title,
                $inputData->url,
                $inputData->publishedAt,
                $inputData->typeValue,
                $inputData->isDisplay,
            );

            $media = $this->repository->save($media);

            $this->recorder->record(
                AuditAction::Create,
                AuditTargetType::Media,
                $media->mediaId,
                $media->toArray(),
            );

            return new CreateOutputData($media);
        });
    }
}
