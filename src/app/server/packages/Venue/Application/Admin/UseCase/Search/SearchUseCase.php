<?php

declare(strict_types=1);

namespace Venue\Application\Admin\UseCase\Search;

use AdminUser\Domain\Models\Permission;
use Support\Optional\Arg;
use Support\Optional\None;
use Support\Optional\Some;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Venue\Domain\Criteria\VenueSearchCriteria;
use Venue\Domain\Models\VenueKind;
use Venue\Domain\Models\VenueRepositoryInterface;

readonly class SearchUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private VenueRepositoryInterface $repository,
    ) {
    }

    public function handle(SearchInputData $inputData): SearchOutputData
    {
        $this->authorizer->authorize(Permission::ReadVenue);

        $criteria = new VenueSearchCriteria(
            $inputData->name === Arg::Optional ? new None() : new Some($inputData->name),
            $inputData->kind === Arg::Optional ? new None() : new Some(VenueKind::from($inputData->kind)),
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
