<?php

declare(strict_types=1);

namespace Person\Application\Admin\UseCase\Update;

use AdminUser\Domain\Models\Permission;
use Person\Domain\Models\PersonId;
use Person\Domain\Models\PersonRepositoryInterface;
use Person\Domain\Services\PersonIntegrityService;
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
        private PersonRepositoryInterface $repository,
        private PersonIntegrityService $service,
        private AuditLogRecorderInterface $recorder,
    ) {
    }

    public function handle(UpdateInputData $inputData): UpdateOutputData
    {
        $this->authorizer->authorize(Permission::WritePerson);

        $personId = new PersonId($inputData->personId);

        return $this->transaction->scope(function () use ($inputData, $personId): UpdateOutputData {
            if (is_null($this->repository->find($personId))) {
                throw new ResourceNotFoundException('Person', $personId->value);
            }

            $person = $this->service->prepareForUpdate($inputData->personId, $inputData->name, $inputData->orderNo);

            $this->repository->save($person);

            $this->recorder->record(
                AuditAction::Update,
                AuditTargetType::Person,
                $person->personId,
                $person->toArray(),
            );

            return new UpdateOutputData($person);
        });
    }
}
