<?php

declare(strict_types=1);

namespace Event\Application\Admin\UseCase\Search;

use AdminUser\Domain\Models\Permission;
use Event\Domain\Criteria\EventSearchCriteria;
use Event\Domain\Models\EventRepositoryInterface;
use Support\UseCase\Authorizer\UseCaseAuthorizer;

readonly class SearchUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private EventRepositoryInterface $repository,
    ) {
    }

    public function handle(SearchInputData $inputData): SearchOutputData
    {
        $this->authorizer->authorize(Permission::ReadEvent);

        $criteria = new EventSearchCriteria(
            $inputData->title,
            $inputData->type,
            $inputData->status,
            $inputData->isDisplay,
            $inputData->sort,
            $inputData->order,
            $inputData->page,
            $inputData->perPage,
        );

        return new SearchOutputData($this->repository->search($criteria), $this->repository->maxPage($criteria));
    }
}
