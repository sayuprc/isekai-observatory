<?php

declare(strict_types=1);

namespace Release\Application\Admin\UseCase\Group\Get;

use AdminUser\Domain\Models\Permission;
use Release\Application\Admin\Query\ReleaseGroupDetailQueryServiceInterface;
use Release\Domain\Models\ReleaseGroupId;
use Release\Domain\Models\ReleaseGroupRepositoryInterface;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Support\UseCase\Exceptions\ResourceNotFoundException;
use Tag\Domain\Models\TagId;
use Tag\Domain\Models\TagReference;
use Tag\Domain\Models\TagRepositoryInterface;

readonly class GetUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private ReleaseGroupRepositoryInterface $repository,
        private ReleaseGroupDetailQueryServiceInterface $query,
        private TagRepositoryInterface $tagRepository,
    ) {
    }

    public function handle(GetInputData $inputData): GetOutputData
    {
        $this->authorizer->authorize(Permission::ReadRelease);

        $releaseGroupId = new ReleaseGroupId($inputData->releaseGroupId);

        if (($found = $this->repository->find($releaseGroupId)) === null) {
            throw new ResourceNotFoundException('ReleaseGroup', $releaseGroupId->value);
        }

        return new GetOutputData(
            $found,
            $this->query->findReferencedReleases($releaseGroupId),
            $this->tagRepository->findByIds(
                ...$found->tags->toGeneric()
                    ->map(static fn (TagReference $tag): TagId => $tag->tagId)
                    ->toArray(),
            ),
        );
    }
}
