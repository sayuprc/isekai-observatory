<?php

declare(strict_types=1);

namespace Person\Application\Admin\UseCase\Group\Update;

use AdminUser\Domain\Models\Permission;
use LogicException;
use Person\Application\Admin\Query\PersonGroupQueryServiceInterface;
use Person\Domain\Models\PersonGroupId;
use Person\Domain\Models\PersonGroupRepositoryInterface;
use Person\Domain\Services\PersonGroupIntegrityService;
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
        private PersonGroupRepositoryInterface $repository,
        private PersonGroupIntegrityService $service,
        private PersonGroupQueryServiceInterface $query,
        private AuditLogRecorderInterface $recorder,
    ) {
    }

    public function handle(UpdateInputData $inputData): UpdateOutputData
    {
        $this->authorizer->authorize(Permission::WritePerson);

        $personGroupId = new PersonGroupId($inputData->personGroupId);

        return $this->transaction->scope(function () use ($inputData, $personGroupId): UpdateOutputData {
            if ($this->repository->find($personGroupId) === null) {
                throw new ResourceNotFoundException('PersonGroup', $personGroupId->value);
            }

            $personGroup = $this->service->prepareForUpdate($inputData->personGroupId, $inputData->name, $inputData->members);

            $this->repository->save($personGroup);

            $this->recorder->record(
                AuditAction::Update,
                AuditTargetType::PersonGroup,
                $personGroup->personGroupId,
                $personGroup->toArray(),
            );

            return new UpdateOutputData(
                $this->query->find($personGroupId) ?? throw new LogicException('保存した人物グループを取得できません'),
            );
        });
    }
}
