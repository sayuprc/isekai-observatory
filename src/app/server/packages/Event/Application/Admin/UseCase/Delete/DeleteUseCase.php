<?php

declare(strict_types=1);

namespace Event\Application\Admin\UseCase\Delete;

use AdminUser\Domain\Models\Permission;
use Event\Domain\Models\EventId;
use Event\Domain\Models\EventRepositoryInterface;
use Support\Contracts\TransactionInterface;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditLogRecorderInterface;
use Support\UseCase\AuditLog\AuditTargetType;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Support\UseCase\Exceptions\ResourceNotFoundException;

readonly class DeleteUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private TransactionInterface $transaction,
        private EventRepositoryInterface $repository,
        private AuditLogRecorderInterface $recorder,
    ) {
    }

    public function handle(DeleteInputData $inputData): void
    {
        $this->authorizer->authorize(Permission::WriteEvent);
        $this->transaction->scope(function () use ($inputData): void {
            $event = $this->repository->find($inputData->eventId);
            if ($event === null) {
                throw new ResourceNotFoundException('Event', $inputData->eventId);
            }
            $this->repository->delete($inputData->eventId);
            $this->recorder->record(AuditAction::Delete, AuditTargetType::Event, new EventId($event->eventId), $event->toArray());
        });
    }
}
