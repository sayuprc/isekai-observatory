<?php

declare(strict_types=1);

namespace Person\Application\Admin\UseCase\Get;

use AdminUser\Domain\Models\Permission;
use Person\Domain\Models\PersonId;
use Person\Domain\Models\PersonRepositoryInterface;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Support\UseCase\Exceptions\ResourceNotFoundException;

readonly class GetUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private PersonRepositoryInterface $repository,
    ) {
    }

    public function handle(GetInputData $inputData): GetOutputData
    {
        $this->authorizer->authorize(Permission::ReadPerson);

        $personId = new PersonId($inputData->personId);

        if (is_null($found = $this->repository->find($personId))) {
            throw new ResourceNotFoundException('Person', $personId->value);
        }

        return new GetOutputData($found);
    }
}
