<?php

declare(strict_types=1);

namespace Person\Application\Admin\UseCase\Search;

use AdminUser\Domain\Models\Permission;
use Person\Application\Admin\Query\PersonUsageCountQueryServiceInterface;
use Person\Domain\Criteria\PersonSearchCriteria;
use Person\Domain\Models\Person;
use Person\Domain\Models\PersonId;
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
        private PersonUsageCountQueryServiceInterface $usageCountQueryService,
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

        $persons = $this->repository->search($criteria);

        return new SearchOutputData(
            $persons,
            $this->repository->maxPage($criteria),
            $this->usageCountQueryService->countUsages(array_values(array_map(static fn (Person $person): PersonId => $person->personId, $persons))),
        );
    }
}
