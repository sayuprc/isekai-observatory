<?php

declare(strict_types=1);

namespace Song\Application\Admin\UseCase\Tag\Get;

use AdminUser\Domain\Models\Permission;
use Song\Domain\Models\Tag\SongTagId;
use Song\Domain\Models\Tag\SongTagRepositoryInterface;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Support\UseCase\Exceptions\ResourceNotFoundException;

readonly class GetUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private SongTagRepositoryInterface $repository,
    ) {
    }

    public function handle(GetInputData $inputData): GetOutputData
    {
        $this->authorizer->authorize(Permission::ReadSong);

        $songTagId = new SongTagId($inputData->songTagId);

        if (is_null($found = $this->repository->find($songTagId))) {
            throw new ResourceNotFoundException('SongTag', $songTagId->value);
        }

        return new GetOutputData($found);
    }
}
