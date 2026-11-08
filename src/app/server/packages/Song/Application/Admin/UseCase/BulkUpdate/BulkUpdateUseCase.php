<?php

declare(strict_types=1);

namespace Song\Application\Admin\UseCase\BulkUpdate;

use AdminUser\Domain\Models\Permission;
use Song\Domain\Models\SongId;
use Song\Domain\Models\SongRepositoryInterface;
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
        private SongRepositoryInterface $repository,
        private AuditLogRecorderInterface $recorder,
        private TagExistenceService $tagExistence,
    ) {
    }

    public function handle(BulkUpdateInputData $inputData): BulkUpdateOutputData
    {
        $this->authorizer->authorize(Permission::WriteSong);

        $changes = TagChanges::fromIds($inputData->addTagIds, $inputData->removeTagIds);

        if ($changes->isEmpty() && $inputData->isDisplay === Arg::Optional) {
            throw new BusinessRuleViolationException('一括更新する内容を指定してください。');
        }

        $songIds = array_map(
            static fn (string $id): SongId => new SongId($id),
            array_values(array_unique($inputData->songIds)),
        );

        return $this->transaction->scope(function () use ($inputData, $changes, $songIds): BulkUpdateOutputData {
            $this->tagExistence->assertAddable($changes);

            $found = [];
            foreach ($this->repository->findByIds(...$songIds) as $record) {
                $found[$record->songId->value] = $record;
            }

            foreach ($songIds as $songId) {
                if (! isset($found[$songId->value])) {
                    throw new ResourceNotFoundException('Song', $songId->value);
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
                    AuditTargetType::Song,
                    $saved->songId,
                    $saved->toArray(),
                );
            }

            return new BulkUpdateOutputData(count($found));
        });
    }
}
