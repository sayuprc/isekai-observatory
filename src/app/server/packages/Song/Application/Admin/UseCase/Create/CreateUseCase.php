<?php

declare(strict_types=1);

namespace Song\Application\Admin\UseCase\Create;

use AdminUser\Domain\Models\Permission;
use Song\Application\Admin\Assemble\SongAssembler;
use Song\Domain\Models\SongRepositoryInterface;
use Song\Domain\Services\SongIntegrityService;
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
        private SongRepositoryInterface $repository,
        private SongIntegrityService $service,
        private SongAssembler $assembler,
        private AuditLogRecorderInterface $recorder,
    ) {
    }

    public function handle(CreateInputData $inputData): CreateOutputData
    {
        $this->authorizer->authorize(Permission::WriteSong);

        return $this->transaction->scope(function () use ($inputData): CreateOutputData {
            $song = $this->service->prepareForCreate(
                $inputData->title,
                $inputData->description,
                $inputData->lyricsLink,
                $inputData->typeValue,
                $inputData->isDisplay,
                $inputData->tags,
                $inputData->persons,
                $inputData->media,
            );

            $song = $this->repository->save($song);

            $this->recorder->record(
                AuditAction::Create,
                AuditTargetType::Song,
                $song->songId,
                $song->toArray(),
            );

            return new CreateOutputData($this->assembler->assemble($song));
        });
    }
}
