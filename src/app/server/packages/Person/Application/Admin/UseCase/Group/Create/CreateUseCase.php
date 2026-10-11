<?php

declare(strict_types=1);

namespace Person\Application\Admin\UseCase\Group\Create;

use AdminUser\Domain\Models\Permission;
use LogicException;
use Person\Application\Admin\Query\PersonGroupQueryServiceInterface;
use Person\Domain\Models\PersonGroupRepositoryInterface;
use Person\Domain\Services\PersonGroupIntegrityService;
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
        private PersonGroupRepositoryInterface $repository,
        private PersonGroupIntegrityService $service,
        private PersonGroupQueryServiceInterface $query,
        private AuditLogRecorderInterface $recorder,
    ) {
    }

    public function handle(CreateInputData $inputData): CreateOutputData
    {
        $this->authorizer->authorize(Permission::WritePerson);

        return $this->transaction->scope(function () use ($inputData): CreateOutputData {
            $personGroup = $this->service->prepareForCreate($inputData->name, $inputData->members);

            $this->repository->save($personGroup);

            $this->recorder->record(
                AuditAction::Create,
                AuditTargetType::PersonGroup,
                $personGroup->personGroupId,
                $personGroup->toArray(),
            );

            return new CreateOutputData(
                $this->query->find($personGroup->personGroupId) ?? throw new LogicException('保存した人物グループを取得できません'),
            );
        });
    }
}
