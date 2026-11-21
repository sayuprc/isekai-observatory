<?php

declare(strict_types=1);

namespace Tag\Application\Admin\UseCase\Get;

use AdminUser\Domain\Models\Permission;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Support\UseCase\Exceptions\ResourceNotFoundException;
use Tag\Domain\Models\TagId;
use Tag\Domain\Models\TagRepositoryInterface;

readonly class GetUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private TagRepositoryInterface $repository,
    ) {
    }

    public function handle(GetInputData $inputData): GetOutputData
    {
        $this->authorizer->authorize(Permission::ReadTag);

        $tagId = new TagId($inputData->tagId);

        if (($found = $this->repository->find($tagId)) === null) {
            throw new ResourceNotFoundException('Tag', $tagId->value);
        }

        return new GetOutputData($found);
    }
}
