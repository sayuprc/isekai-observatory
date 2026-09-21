<?php

declare(strict_types=1);

namespace AdminUser\Application\Admin\UseCase\List;

use AdminUser\Domain\Models\AdminUserRepositoryInterface;
use AdminUser\Domain\Models\Permission;
use Support\UseCase\Authorizer\UseCaseAuthorizer;

readonly class ListUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private AdminUserRepositoryInterface $repository,
    ) {
    }

    public function handle(): ListOutputData
    {
        $this->authorizer->authorize(Permission::ReadAdminUser);

        return new ListOutputData($this->repository->all());
    }
}
