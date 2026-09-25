<?php

declare(strict_types=1);

namespace Event\Application\Admin\UseCase\Search;

use AdminUser\Domain\Models\Permission;
use Event\Application\Admin\Query\EventSearchQueryServiceInterface;
use Event\Domain\Criteria\EventSearchCriteria;
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
        private EventSearchQueryServiceInterface $queryService,
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

        return new SearchOutputData($this->queryService->search($criteria), $this->queryService->maxPage($criteria));
    }
}
