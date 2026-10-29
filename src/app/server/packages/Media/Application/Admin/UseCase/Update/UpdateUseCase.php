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
use Tag\Domain\Models\TagId;
use Tag\Domain\Models\TagReference;
use Tag\Domain\Models\TagRepositoryInterface;

readonly class UpdateUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private TransactionInterface $transaction,
        private MediaRepositoryInterface $repository,
        private MediaIntegrityService $service,
        private AuditLogRecorderInterface $recorder,
        private TagRepositoryInterface $tagRepository,
    ) {
    }

    public function handle(UpdateInputData $inputData): UpdateOutputData
    {
        $this->authorizer->authorize(Permission::WriteMedia);

        $mediaId = new MediaId($inputData->mediaId);

        return $this->transaction->scope(function () use ($inputData, $mediaId): UpdateOutputData {
            if ($this->repository->find($mediaId) === null) {
                throw new ResourceNotFoundException('Media', $mediaId->value);
            }

            $media = $this->service->prepareForUpdate(
                $inputData->mediaId,
                $inputData->title,
                $inputData->url,
                $inputData->publishedAt,
                $inputData->type,
                $inputData->isDisplay,
                $inputData->tags,
            );

            $media = $this->repository->save($media);

            $this->recorder->record(
                AuditAction::Update,
                AuditTargetType::Media,
                $media->mediaId,
                $media->toArray(),
            );

            return new UpdateOutputData(
                $media,
                $this->tagRepository->findByIds(
                    ...$media->tags->toGeneric()
                        ->map(static fn (TagReference $tag): TagId => $tag->tagId)
                        ->toArray(),
                ),
            );
        });
    }
}
