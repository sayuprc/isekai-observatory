<?php

declare(strict_types=1);

namespace Tag\Application\Admin\UseCase\Search;

use AdminUser\Domain\Models\Permission;
use Support\Optional\Arg;
use Support\Optional\None;
use Support\Optional\Some;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Tag\Application\Admin\Query\TagUsageCountQueryServiceInterface;
use Tag\Domain\Criteria\TagSearchCriteria;
use Tag\Domain\Models\Tag;
use Tag\Domain\Models\TagId;
use Tag\Domain\Models\TagRepositoryInterface;

readonly class SearchUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private TagRepositoryInterface $repository,
        private TagUsageCountQueryServiceInterface $usageCountQueryService,
    ) {
    }

    public function handle(SearchInputData $inputData): SearchOutputData
    {
        $this->authorizer->authorize(Permission::ReadTag);

        $criteria = new TagSearchCriteria(
            $inputData->name === Arg::Optional
                ? new None()
                : new Some($inputData->name),
            $inputData->sort,
            $inputData->order,
            $inputData->page,
            $inputData->perPage,
        );

        $tags = $this->repository->search($criteria);

        return new SearchOutputData(
            $tags,
            $this->repository->maxPage($criteria),
            $this->usageCountQueryService->countUsages(array_values(array_map(static fn (Tag $tag): TagId => $tag->tagId, $tags))),
        );
    }
}
