<?php

declare(strict_types=1);

namespace Song\Application\Admin\UseCase\Tag\Update;

use AdminUser\Domain\Models\Permission;
use Song\Domain\Models\Tag\SongTagId;
use Song\Domain\Models\Tag\SongTagRepositoryInterface;
use Song\Domain\Services\SongTagIntegrityService;
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
        private SongTagRepositoryInterface $repository,
        private SongTagIntegrityService $service,
        private AuditLogRecorderInterface $recorder,
    ) {
    }

    public function handle(UpdateInputData $inputData): UpdateOutputData
    {
        $this->authorizer->authorize(Permission::WriteSong);

        $songTagId = new SongTagId($inputData->songTagId);

        return $this->transaction->scope(function () use ($inputData, $songTagId): UpdateOutputData {
            if (is_null($this->repository->find($songTagId))) {
                throw new ResourceNotFoundException('SongTag', $songTagId->value);
            }

            $tag = $this->service->prepareForUpdate($inputData->songTagId, $inputData->name, $inputData->orderNo);

            $this->repository->save($tag);

            $this->recorder->record(
                AuditAction::Update,
                AuditTargetType::SongTag,
                $tag->songTagId,
                $tag->toArray(),
            );

            return new UpdateOutputData($tag);
        });
    }
}
