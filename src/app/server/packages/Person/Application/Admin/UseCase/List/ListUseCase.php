<?php

declare(strict_types=1);

namespace Person\Application\Admin\UseCase\List;

use AdminUser\Domain\Models\Permission;
use Person\Domain\Models\PersonRepositoryInterface;
use Support\UseCase\Authorizer\UseCaseAuthorizer;

readonly class ListUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private PersonRepositoryInterface $repository,
    ) {
    }

    public function handle(): ListOutputData
    {
        $this->authorizer->authorize(Permission::ReadPerson);

        return new ListOutputData($this->repository->all());
    }
}
