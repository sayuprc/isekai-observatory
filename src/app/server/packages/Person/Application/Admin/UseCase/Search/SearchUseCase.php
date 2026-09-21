<?php

declare(strict_types=1);

namespace Person\Application\Admin\UseCase\Search;

use AdminUser\Domain\Models\Permission;
use Person\Domain\Criteria\PersonSearchCriteria;
use Person\Domain\Models\PersonRepositoryInterface;
use Support\Optional\Arg;
use Support\Optional\None;
use Support\Optional\Some;
use Support\UseCase\Authorizer\UseCaseAuthorizer;

readonly class SearchUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private PersonRepositoryInterface $repository,
    ) {
    }

    public function handle(SearchInputData $inputData): SearchOutputData
    {
        $this->authorizer->authorize(Permission::ReadPerson);

        $criteria = new PersonSearchCriteria(
            $inputData->name === Arg::Optional
                ? new None()
                : new Some($inputData->name),
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
