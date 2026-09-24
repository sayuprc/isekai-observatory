<?php

declare(strict_types=1);

namespace Event\Application\Admin\UseCase\Search;

use AdminUser\Domain\Models\Permission;
use Event\Domain\Criteria\EventSearchCriteria;
use Event\Domain\Models\EventRepositoryInterface;
use Event\Domain\Models\EventStatus;
use Event\Domain\Models\EventType;
use Support\Optional\Arg;
use Support\Optional\None;
use Support\Optional\Some;
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
            $inputData->title === Arg::Optional
                ? new None()
                : new Some($inputData->title),
            $inputData->type === Arg::Optional
                ? new None()
                : new Some(EventType::from($inputData->type)),
            $inputData->status === Arg::Optional
                ? new None()
                : new Some(EventStatus::from($inputData->status)),
            $inputData->isDisplay === Arg::Optional
                ? new None()
                : new Some($inputData->isDisplay),
            $inputData->sort,
            $inputData->order,
            $inputData->page,
            $inputData->perPage,
        );

        return new SearchOutputData($this->repository->search($criteria), $this->repository->maxPage($criteria));
    }
}
