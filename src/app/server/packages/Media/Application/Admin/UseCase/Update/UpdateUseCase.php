<?php

declare(strict_types=1);

namespace Media\Application\Admin\UseCase\Update;

use AdminUser\Domain\Models\Permission;
use Media\Domain\Models\MediaId;
use Media\Domain\Models\MediaRepositoryInterface;
use Media\Domain\Services\MediaIntegrityService;
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
        private MediaRepositoryInterface $repository,
        private MediaIntegrityService $service,
        private AuditLogRecorderInterface $recorder,
    ) {
    }

    public function handle(UpdateInputData $inputData): UpdateOutputData
    {
        $this->authorizer->authorize(Permission::WriteMedia);

        $mediaId = new MediaId($inputData->mediaId);

        return $this->transaction->scope(function () use ($inputData, $mediaId): UpdateOutputData {
            if (is_null($this->repository->find($mediaId))) {
                throw new ResourceNotFoundException('Media', $mediaId->value);
            }

            $media = $this->service->prepareForUpdate(
                $inputData->mediaId,
                $inputData->title,
                $inputData->url,
                $inputData->publishedAt,
                $inputData->typeValue,
                $inputData->isDisplay,
            );

            $media = $this->repository->save($media);

            $this->recorder->record(
                AuditAction::Update,
                AuditTargetType::Media,
                $media->mediaId,
                $media->toArray(),
            );

            return new UpdateOutputData($media);
        });
    }
}
