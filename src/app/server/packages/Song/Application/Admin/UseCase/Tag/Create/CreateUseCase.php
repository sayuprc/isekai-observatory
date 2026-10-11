<?php

declare(strict_types=1);

namespace Song\Application\Admin\UseCase\Tag\Create;

use AdminUser\Domain\Models\Permission;
use Song\Domain\Models\Tag\SongTagRepositoryInterface;
use Song\Domain\Services\SongTagIntegrityService;
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
        private SongTagRepositoryInterface $repository,
        private SongTagIntegrityService $service,
        private AuditLogRecorderInterface $recorder,
    ) {
    }

    public function handle(CreateInputData $inputData): CreateOutputData
    {
        $this->authorizer->authorize(Permission::WriteSong);

        return $this->transaction->scope(function () use ($inputData): CreateOutputData {
            $tag = $this->service->prepareForCreate($inputData->name);

            $this->repository->save($tag);

            $this->recorder->record(
                AuditAction::Create,
                AuditTargetType::SongTag,
                $tag->songTagId,
                $tag->toArray(),
            );

            return new CreateOutputData($tag);
        });
    }
}
