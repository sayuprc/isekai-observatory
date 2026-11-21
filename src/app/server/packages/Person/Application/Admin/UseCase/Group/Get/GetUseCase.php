<?php

declare(strict_types=1);

namespace Person\Application\Admin\UseCase\Group\Get;

use AdminUser\Domain\Models\Permission;
use Person\Application\Admin\Query\PersonGroupQueryServiceInterface;
use Person\Domain\Models\PersonGroupId;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Support\UseCase\Exceptions\ResourceNotFoundException;

readonly class GetUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private PersonGroupQueryServiceInterface $query,
    ) {
    }

    public function handle(GetInputData $inputData): GetOutputData
    {
        $this->authorizer->authorize(Permission::ReadPerson);

        $personGroupId = new PersonGroupId($inputData->personGroupId);

        if (($found = $this->query->find($personGroupId)) === null) {
            throw new ResourceNotFoundException('PersonGroup', $personGroupId->value);
        }

        return new GetOutputData($found);
    }
}
