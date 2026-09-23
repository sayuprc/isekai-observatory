<?php

declare(strict_types=1);

namespace Event\Application\Admin\UseCase\Update;

use AdminUser\Domain\Models\Permission;
use Event\Application\Admin\Assemble\EventAssembler;
use Event\Domain\Models\EventId;
use Event\Domain\Models\EventRepositoryInterface;
use Event\Domain\Services\EventIntegrityService;
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
        private EventRepositoryInterface $repository,
        private EventIntegrityService $integrityService,
        private EventAssembler $assembler,
        private AuditLogRecorderInterface $recorder,
    ) {
    }

    public function handle(UpdateInputData $inputData): UpdateOutputData
    {
        $this->authorizer->authorize(Permission::WriteEvent);

        return $this->transaction->scope(function () use ($inputData): UpdateOutputData {
            if ($this->repository->find(new EventId($inputData->eventId)) === null) {
                throw new ResourceNotFoundException('Event', $inputData->eventId);
            }

            $event = $this->integrityService->prepareForUpdate(
                $inputData->eventId,
                $inputData->title,
                $inputData->description,
                $inputData->typeValue,
                $inputData->schedule,
                $inputData->statusValue,
                $inputData->isDisplay,
                $inputData->venueIds,
                $inputData->mediaIds,
                $inputData->sources,
                $inputData->performances,
                $inputData->setlist,
            );

            $event = $this->repository->save($event);

            $this->recorder->record(
                AuditAction::Update,
                AuditTargetType::Event,
                $event->eventId,
                $event->toArray(),
            );

            return new UpdateOutputData($this->assembler->assemble($event));
        });
    }
}
