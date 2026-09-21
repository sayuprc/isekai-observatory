<?php

declare(strict_types=1);

namespace Release\Application\Admin\UseCase\Get;

use AdminUser\Domain\Models\Permission;
use Release\Application\Admin\Query\ReleaseDetailQueryServiceInterface;
use Release\Domain\Models\ReleaseGroupRepositoryInterface;
use Release\Domain\Models\ReleaseId;
use Release\Domain\Models\ReleaseRepositoryInterface;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Support\UseCase\Exceptions\ResourceNotFoundException;

readonly class GetUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private ReleaseRepositoryInterface $repository,
        private ReleaseGroupRepositoryInterface $groupRepository,
        private ReleaseDetailQueryServiceInterface $query,
    ) {
    }

    public function handle(GetInputData $inputData): GetOutputData
    {
        $this->authorizer->authorize(Permission::ReadRelease);

        $releaseId = new ReleaseId($inputData->releaseId);

        if (is_null($found = $this->repository->find($releaseId))) {
            throw new ResourceNotFoundException('Release', $releaseId->value);
        }

        if (is_null($group = $this->groupRepository->find($found->releaseGroupId))) {
            throw new ResourceNotFoundException('ReleaseGroup', $found->releaseGroupId->value);
        }

        return new GetOutputData(
            $found,
            $group,
            $this->query->findReferencedSongs($releaseId),
        );
    }
}
