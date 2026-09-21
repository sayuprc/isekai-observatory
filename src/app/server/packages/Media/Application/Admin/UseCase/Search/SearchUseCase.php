<?php

declare(strict_types=1);

namespace Media\Application\Admin\UseCase\Search;

use AdminUser\Domain\Models\Permission;
use Media\Domain\Criteria\MediaSearchCriteria;
use Media\Domain\Models\MediaRepositoryInterface;
use Media\Domain\Models\MediaType;
use Support\Optional\Arg;
use Support\Optional\None;
use Support\Optional\Some;
use Support\UseCase\Authorizer\UseCaseAuthorizer;

readonly class SearchUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private MediaRepositoryInterface $repository,
    ) {
    }

    public function handle(SearchInputData $inputData): SearchOutputData
    {
        $this->authorizer->authorize(Permission::ReadMedia);

        $criteria = new MediaSearchCriteria(
            $inputData->title === Arg::Optional
                ? new None()
                : new Some($inputData->title),
            $inputData->type === Arg::Optional
                ? new None()
                : new Some(MediaType::from($inputData->type)),
            $inputData->isDisplay === Arg::Optional
                ? new None()
                : new Some($inputData->isDisplay),
            $inputData->sort,
            $inputData->order,
            $inputData->page,
            $inputData->perPage,
        );

        return new SearchOutputData(
            $this->repository->search($criteria),
            $this->repository->maxPage($criteria),
        );
    }
}
