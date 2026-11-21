<?php

declare(strict_types=1);

namespace Person\Application\Admin\Group\Search;

use AdminUser\Domain\Models\Permission;
use Person\Application\Admin\Group\Query\PersonGroupQueryServiceInterface;
use Person\Application\Admin\Group\Query\PersonGroupSummary;
use Person\Application\Admin\Group\Search\Query\PersonGroupUsageCountQueryServiceInterface;
use Person\Domain\Criteria\PersonGroupSearchCriteria;
use Support\Optional\Arg;
use Support\Optional\None;
use Support\Optional\Some;
use Support\UseCase\Authorizer\UseCaseAuthorizer;

readonly class SearchUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private PersonGroupQueryServiceInterface $query,
        private PersonGroupUsageCountQueryServiceInterface $usageCountQueryService,
    ) {
    }

    public function handle(SearchInputData $inputData): SearchOutputData
    {
        $this->authorizer->authorize(Permission::ReadPerson);

        $criteria = new PersonGroupSearchCriteria(
            $inputData->name === Arg::Optional
                ? new None()
                : new Some($inputData->name),
            $inputData->page,
            $inputData->perPage,
        );

        $personGroups = $this->query->search($criteria);

        return new SearchOutputData(
            $personGroups,
            $this->query->maxPage($criteria),
            $this->usageCountQueryService->countPerformances(
                array_map(
                    static fn (PersonGroupSummary $personGroup): string => $personGroup->personGroupId,
                    $personGroups,
                ),
            ),
        );
    }
}
