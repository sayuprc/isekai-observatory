<?php

declare(strict_types=1);

namespace Release\Application\Admin\UseCase\Group\BulkUpdate;

use AdminUser\Domain\Models\Permission;
use Release\Domain\Models\ReleaseGroupId;
use Release\Domain\Models\ReleaseGroupRepositoryInterface;
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
        private ReleaseGroupRepositoryInterface $repository,
        private AuditLogRecorderInterface $recorder,
        private TagExistenceService $tagExistence,
    ) {
    }

    public function handle(BulkUpdateInputData $inputData): BulkUpdateOutputData
    {
        $this->authorizer->authorize(Permission::WriteRelease);

        $changes = TagChanges::fromIds($inputData->addTagIds, $inputData->removeTagIds);

        if ($changes->isEmpty() && $inputData->isDisplay === Arg::Optional) {
            throw new BusinessRuleViolationException('一括更新する内容を指定してください。');
        }

        $releaseGroupIds = array_map(
            static fn (string $id): ReleaseGroupId => new ReleaseGroupId($id),
            array_values(array_unique($inputData->releaseGroupIds)),
        );

        return $this->transaction->scope(function () use (
            $inputData,
            $changes,
            $releaseGroupIds,
        ): BulkUpdateOutputData {
            $this->tagExistence->assertAddable($changes);

            $found = [];
            foreach ($this->repository->findByIds(...$releaseGroupIds) as $record) {
                $found[$record->releaseGroupId->value] = $record;
            }

            foreach ($releaseGroupIds as $releaseGroupId) {
                if (! isset($found[$releaseGroupId->value])) {
                    throw new ResourceNotFoundException('ReleaseGroup', $releaseGroupId->value);
                }
            }

            foreach ($found as $record) {
                $updated = $record->withTags($changes->apply($record->tags));

                if ($inputData->isDisplay !== Arg::Optional) {
                    $updated = $updated->withDisplay($inputData->isDisplay);
                }

                $saved = $this->repository->save($updated);

                $this->recorder->record(
                    AuditAction::Update,
                    AuditTargetType::ReleaseGroup,
                    $saved->releaseGroupId,
                    $saved->toArray(),
                );
            }

            return new BulkUpdateOutputData(count($found));
        });
    }
}
