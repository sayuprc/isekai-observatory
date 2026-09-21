<?php

declare(strict_types=1);

namespace Media\Application\Admin\UseCase\Get;

use AdminUser\Domain\Models\Permission;
use Media\Application\Admin\Query\MediaDetailQueryServiceInterface;
use Media\Domain\Models\MediaId;
use Media\Domain\Models\MediaRepositoryInterface;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Support\UseCase\Exceptions\ResourceNotFoundException;

readonly class GetUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private MediaRepositoryInterface $repository,
        private MediaDetailQueryServiceInterface $query,
    ) {
    }

    public function handle(GetInputData $inputData): GetOutputData
    {
        $this->authorizer->authorize(Permission::ReadMedia);

        $mediaId = new MediaId($inputData->mediaId);

        if (is_null($found = $this->repository->find($mediaId))) {
            throw new ResourceNotFoundException('Media', $mediaId->value);
        }

        return new GetOutputData(
            $found,
            $this->query->findReferencedSongs($mediaId),
        );
    }
}
