<?php

declare(strict_types=1);

namespace Song\Application\Admin\UseCase\Tag\List;

use AdminUser\Domain\Models\Permission;
use Song\Domain\Models\Tag\SongTagRepositoryInterface;
use Support\UseCase\Authorizer\UseCaseAuthorizer;

readonly class ListUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private SongTagRepositoryInterface $repository,
    ) {
    }

    public function handle(): ListOutputData
    {
        $this->authorizer->authorize(Permission::ReadSong);

        return new ListOutputData($this->repository->all());
    }
}
