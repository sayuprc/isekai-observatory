<?php

declare(strict_types=1);

namespace Event\Application\Admin\UseCase\Create;

use AdminUser\Domain\Models\Permission;
use Event\Domain\Models\EventId;
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
        private AuditLogRecorderInterface $recorder,
    ) {
    }

    public function handle(CreateInputData $inputData): CreateOutputData
    {
        $this->authorizer->authorize(Permission::WriteEvent);

        $output = $this->transaction->scope(function () use ($inputData): CreateOutputData {
            $event = $this->integrityService->prepareForCreate([
                'title' => $inputData->title,
                'description' => $inputData->description,
                'typeValue' => $inputData->typeValue,
                'schedule' => $inputData->schedule,
                'statusValue' => $inputData->statusValue,
                'isDisplay' => $inputData->isDisplay,
                'venueIds' => $inputData->venueIds,
                'mediaIds' => $inputData->mediaIds,
                'sources' => $inputData->sources,
                'performances' => $inputData->performances,
                'setlist' => $inputData->setlist,
            ]);
            $this->repository->save($event);
            $this->recorder->record(AuditAction::Create, AuditTargetType::Event, new EventId($event->eventId), $event->toArray());

            return new CreateOutputData($event);
        });

        return new CreateOutputData($this->repository->find($output->event->eventId) ?? $output->event);
    }
}
