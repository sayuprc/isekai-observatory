<?php

declare(strict_types=1);

namespace Release\Application\Admin\UseCase\Search;

use Release\Application\Admin\UseCase\Search\Query\ReleaseSearchQueryServiceInterface;
use Release\Domain\Criteria\ReleaseSearchCriteria;
use Support\Domain\Permission;
use Support\Optional\Arg;
use Support\Optional\None;
use Support\Optional\Some;
use Support\UseCase\Authorizer\UseCaseAuthorizer;

readonly class SearchUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private ReleaseSearchQueryServiceInterface $query,
    ) {
    }

    public function handle(SearchInputData $inputData): SearchOutputData
    {
        $this->authorizer->authorize(Permission::ReadRelease);

        $criteria = new ReleaseSearchCriteria(
            $inputData->title === Arg::Optional
                ? new None()
                : new Some($inputData->title),
            $inputData->page,
            $inputData->perPage,
        );

        return new SearchOutputData(
            $this->query->search($criteria),
            $this->query->maxPage($criteria),
        );
    }
}
