<?php

declare(strict_types=1);

namespace Person\Application\Admin\UseCase\Create;

use AdminUser\Domain\Models\Permission;
use Person\Domain\Models\PersonRepositoryInterface;
use Person\Domain\Services\PersonIntegrityService;
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
        private PersonRepositoryInterface $repository,
        private PersonIntegrityService $service,
        private AuditLogRecorderInterface $recorder,
    ) {
    }

    public function handle(CreateInputData $inputData): CreateOutputData
    {
        $this->authorizer->authorize(Permission::WritePerson);

        return $this->transaction->scope(function () use ($inputData): CreateOutputData {
            $person = $this->service->prepareForCreate($inputData->name);

            $this->repository->save($person);

            $this->recorder->record(
                AuditAction::Create,
                AuditTargetType::Person,
                $person->personId,
                $person->toArray(),
            );

            return new CreateOutputData($person);
        });
    }
}
