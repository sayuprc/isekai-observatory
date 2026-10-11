<?php

declare(strict_types=1);

namespace Release\Application\Admin\UseCase\Group\Search;

use AdminUser\Domain\Models\Permission;
use Release\Application\Admin\Query\ReleaseGroupSearchQueryServiceInterface;
use Release\Domain\Criteria\ReleaseGroupSearchCriteria;
use Release\Domain\Models\ReleaseGroupType;
use Support\Optional\Arg;
use Support\Optional\None;
use Support\Optional\Some;
use Support\UseCase\Authorizer\UseCaseAuthorizer;

readonly class SearchUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private ReleaseGroupSearchQueryServiceInterface $query,
    ) {
    }

    public function handle(SearchInputData $inputData): SearchOutputData
    {
        $this->authorizer->authorize(Permission::ReadRelease);

        $criteria = new ReleaseGroupSearchCriteria(
            $inputData->title === Arg::Optional
                ? new None()
                : new Some($inputData->title),
            $inputData->type === Arg::Optional
                ? new None()
                : new Some(ReleaseGroupType::from($inputData->type)),
            $inputData->isDisplay === Arg::Optional
                ? new None()
                : new Some($inputData->isDisplay),
            $inputData->sort,
            $inputData->order,
            $inputData->page,
            $inputData->perPage,
        );

        return new SearchOutputData(
            $this->query->search($criteria),
            $this->query->maxPage($criteria),
        );
    }
}
