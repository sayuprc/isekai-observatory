<?php

declare(strict_types=1);

namespace Tag\Application\Admin\UseCase\List;

use AdminUser\Domain\Models\Permission;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Tag\Domain\Models\TagRepositoryInterface;

readonly class ListUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private TagRepositoryInterface $repository,
    ) {
    }

    public function handle(): ListOutputData
    {
        $this->authorizer->authorize(Permission::ReadTag);

        return new ListOutputData($this->repository->all());
    }
}
