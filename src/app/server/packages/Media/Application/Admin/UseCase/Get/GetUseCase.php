<?php

declare(strict_types=1);

namespace Media\Application\Admin\UseCase\Get;

use AdminUser\Domain\Models\Permission;
use Media\Application\Admin\Query\MediaDetailQueryServiceInterface;
use Media\Domain\Models\MediaId;
use Media\Domain\Models\MediaRepositoryInterface;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Support\UseCase\Exceptions\ResourceNotFoundException;
use Tag\Domain\Models\TagId;
use Tag\Domain\Models\TagReference;
use Tag\Domain\Models\TagRepositoryInterface;

readonly class GetUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private MediaRepositoryInterface $repository,
        private MediaDetailQueryServiceInterface $query,
        private TagRepositoryInterface $tagRepository,
    ) {
    }

    public function handle(GetInputData $inputData): GetOutputData
    {
        $this->authorizer->authorize(Permission::ReadMedia);

        $mediaId = new MediaId($inputData->mediaId);

        if (($found = $this->repository->find($mediaId)) === null) {
            throw new ResourceNotFoundException('Media', $mediaId->value);
        }

        return new GetOutputData(
            $found,
            $this->query->findReferencedSongs($mediaId),
            $this->tagRepository->findByIds(...$found->tags->toGeneric()->map(static fn (TagReference $tag): TagId => $tag->tagId)->toArray()),
        );
    }
}
