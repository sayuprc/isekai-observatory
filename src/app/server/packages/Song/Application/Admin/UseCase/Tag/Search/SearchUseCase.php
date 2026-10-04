<?php

declare(strict_types=1);

namespace Song\Application\Admin\UseCase\Tag\Search;

use AdminUser\Domain\Models\Permission;
use Song\Application\Admin\Query\Tag\SongTagUsageCountQueryServiceInterface;
use Song\Domain\Criteria\Tag\SongTagSearchCriteria;
use Song\Domain\Models\Tag\SongTag;
use Song\Domain\Models\Tag\SongTagId;
use Song\Domain\Models\Tag\SongTagRepositoryInterface;
use Support\Optional\Arg;
use Support\Optional\None;
use Support\Optional\Some;
use Support\UseCase\Authorizer\UseCaseAuthorizer;

readonly class SearchUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private SongTagRepositoryInterface $repository,
        private SongTagUsageCountQueryServiceInterface $usageCountQueryService,
    ) {
    }

    public function handle(SearchInputData $inputData): SearchOutputData
    {
        $this->authorizer->authorize(Permission::ReadSong);

        $criteria = new SongTagSearchCriteria(
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
            $this->usageCountQueryService->countSongs(array_values(array_map(static fn (SongTag $tag): SongTagId => $tag->songTagId, $tags))),
        );
    }
}
