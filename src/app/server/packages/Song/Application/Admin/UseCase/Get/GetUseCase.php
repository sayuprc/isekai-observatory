<?php

declare(strict_types=1);

namespace Song\Application\Admin\UseCase\Get;

use AdminUser\Domain\Models\Permission;
use Song\Application\Admin\Assemble\SongAssembler;
use Song\Domain\Models\SongId;
use Song\Domain\Models\SongRepositoryInterface;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Support\UseCase\Exceptions\ResourceNotFoundException;

readonly class GetUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private SongRepositoryInterface $repository,
        private SongAssembler $assembler,
    ) {
    }

    public function handle(GetInputData $inputData): GetOutputData
    {
        $this->authorizer->authorize(Permission::ReadSong);

        $songId = new SongId($inputData->songId);

        if (is_null($found = $this->repository->find($songId))) {
            throw new ResourceNotFoundException('楽曲', $songId->value);
        }

        return new GetOutputData($this->assembler->assemble($found));
    }
}
