<?php

declare(strict_types=1);

namespace Song\Application\Admin\UseCase\Update;

use AdminUser\Domain\Models\Permission;
use Song\Application\Admin\Assemble\SongAssembler;
use Song\Domain\Models\SongId;
use Song\Domain\Models\SongRepositoryInterface;
use Song\Domain\Services\SongIntegrityService;
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
        private SongRepositoryInterface $repository,
        private SongIntegrityService $service,
        private SongAssembler $assembler,
        private AuditLogRecorderInterface $recorder,
    ) {
    }

    public function handle(UpdateInputData $inputData): UpdateOutputData
    {
        $this->authorizer->authorize(Permission::WriteSong);

        $songId = new SongId($inputData->songId);

        return $this->transaction->scope(function () use ($inputData, $songId): UpdateOutputData {
            if (is_null($this->repository->find($songId))) {
                throw new ResourceNotFoundException('Song', $songId->value);
            }

            $song = $this->service->prepareForUpdate(
                $inputData->songId,
                $inputData->title,
                $inputData->description,
                $inputData->lyricsLink,
                $inputData->typeValue,
                $inputData->isDisplay,
                $inputData->orderNo,
                $inputData->tags,
                $inputData->persons,
                $inputData->media,
            );

            $song = $this->repository->save($song);

            $this->recorder->record(
                AuditAction::Update,
                AuditTargetType::Song,
                $song->songId,
                $song->toArray(),
            );

            return new UpdateOutputData($this->assembler->assemble($song));
        });
    }
}
