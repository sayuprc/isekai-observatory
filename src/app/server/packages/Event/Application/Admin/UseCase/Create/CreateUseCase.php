<?php

declare(strict_types=1);

namespace Event\Application\Admin\UseCase\Create;

use AdminUser\Domain\Models\Permission;
use Event\Application\Admin\Assemble\EventAssembler;
use Event\Domain\Models\EventRepositoryInterface;
use Event\Domain\Services\EventIntegrityService;
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
        private EventRepositoryInterface $repository,
        private EventIntegrityService $integrityService,
        private EventAssembler $assembler,
        private AuditLogRecorderInterface $recorder,
    ) {
    }

    public function handle(CreateInputData $inputData): CreateOutputData
    {
        $this->authorizer->authorize(Permission::WriteEvent);

        return $this->transaction->scope(function () use ($inputData): CreateOutputData {
            $event = $this->integrityService->prepareForCreate(
                $inputData->title,
                $inputData->description,
                $inputData->typeValue,
                $inputData->schedule,
                $inputData->statusValue,
                $inputData->isDisplay,
                $inputData->venues,
                $inputData->media,
                $inputData->sources,
                $inputData->performances,
                $inputData->setlist,
            );

            $event = $this->repository->save($event);

            $this->recorder->record(
                AuditAction::Create,
                AuditTargetType::Event,
                $event->eventId,
                $event->toArray(),
            );

            return new CreateOutputData($this->assembler->assemble($event));
        });
    }
}
