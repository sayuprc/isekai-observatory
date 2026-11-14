<?php

declare(strict_types=1);

namespace Event\Application\Admin\UseCase\BulkUpdate;

use AdminUser\Domain\Models\Permission;
use Event\Domain\Models\EventId;
use Event\Domain\Models\EventRepositoryInterface;
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
        private EventRepositoryInterface $repository,
        private AuditLogRecorderInterface $recorder,
        private TagExistenceService $tagExistence,
    ) {
    }

    public function handle(BulkUpdateInputData $inputData): BulkUpdateOutputData
    {
        $this->authorizer->authorize(Permission::WriteEvent);

        $changes = TagChanges::fromIds($inputData->addTagIds, $inputData->removeTagIds);

        if ($changes->isEmpty() && $inputData->isDisplay === Arg::Optional) {
            throw new BusinessRuleViolationException('一括更新する内容を指定してください。');
        }

        $eventIds = array_map(
            static fn (string $id): EventId => new EventId($id),
            array_values(array_unique($inputData->eventIds)),
        );

        return $this->transaction->scope(function () use ($inputData, $changes, $eventIds): BulkUpdateOutputData {
            $this->tagExistence->assertAddable($changes);

            $found = [];
            foreach ($this->repository->findByIds(...$eventIds) as $record) {
                $found[$record->eventId->value] = $record;
            }

            foreach ($eventIds as $eventId) {
                if (! isset($found[$eventId->value])) {
                    throw new ResourceNotFoundException('Event', $eventId->value);
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
                    AuditTargetType::Event,
                    $saved->eventId,
                    $saved->toArray(),
                );
            }

            return new BulkUpdateOutputData(count($found));
        });
    }
}
