<?php

declare(strict_types=1);

namespace Media\Application\Admin\UseCase\BulkUpdate;

use AdminUser\Domain\Models\Permission;
use Media\Domain\Models\MediaId;
use Media\Domain\Models\MediaRepositoryInterface;
use Support\Contracts\TransactionInterface;
use Support\Domain\Exceptions\BusinessRuleViolationException;
use Support\Optional\Arg;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditLogRecorderInterface;
use Support\UseCase\AuditLog\AuditTargetType;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Support\UseCase\Exceptions\ResourceNotFoundException;
use Tag\Domain\Models\TagChanges;
use Tag\Domain\Services\TagExistenceService;

readonly class BulkUpdateUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private TransactionInterface $transaction,
        private MediaRepositoryInterface $repository,
        private AuditLogRecorderInterface $recorder,
        private TagExistenceService $tagExistence,
    ) {
    }

    public function handle(BulkUpdateInputData $inputData): BulkUpdateOutputData
    {
        $this->authorizer->authorize(Permission::WriteMedia);

        $changes = TagChanges::fromIds($inputData->addTagIds, $inputData->removeTagIds);

        if ($changes->isEmpty() && $inputData->isDisplay === Arg::Optional) {
            throw new BusinessRuleViolationException('一括更新する内容を指定してください。');
        }

        $mediaIds = array_map(
            static fn (string $id): MediaId => new MediaId($id),
            array_values(array_unique($inputData->mediaIds)),
        );

        return $this->transaction->scope(function () use ($inputData, $changes, $mediaIds): BulkUpdateOutputData {
            $this->tagExistence->assertAddable($changes);

            $found = [];
            foreach ($this->repository->findByIds(...$mediaIds) as $media) {
                $found[$media->mediaId->value] = $media;
            }

            foreach ($mediaIds as $mediaId) {
                if (! isset($found[$mediaId->value])) {
                    throw new ResourceNotFoundException('Media', $mediaId->value);
                }
            }

            foreach ($found as $media) {
                $updated = $media->withTags($changes->apply($media->tags));

                if ($inputData->isDisplay !== Arg::Optional) {
                    $updated = $updated->withDisplay($inputData->isDisplay);
                }

                $saved = $this->repository->save($updated);

                $this->recorder->record(
                    AuditAction::Update,
                    AuditTargetType::Media,
                    $saved->mediaId,
                    $saved->toArray(),
                );
            }

            return new BulkUpdateOutputData(count($found));
        });
    }
}
