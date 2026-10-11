<?php

declare(strict_types=1);

namespace Song\Application\Admin\UseCase\Search;

use AdminUser\Domain\Models\Permission;
use Song\Application\Admin\Query\SongQueryServiceInterface;
use Song\Domain\Criteria\SongSearchCriteria;
use Song\Domain\Models\SongType;
use Support\Optional\Arg;
use Support\Optional\None;
use Support\Optional\Some;
use Support\UseCase\Authorizer\UseCaseAuthorizer;

readonly class SearchUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private SongQueryServiceInterface $query,
    ) {
    }

    public function handle(SearchInputData $inputData): SearchOutputData
    {
        $this->authorizer->authorize(Permission::ReadSong);

        $criteria = new SongSearchCriteria(
            $inputData->title === Arg::Optional
                ? new None()
                : new Some($inputData->title),
            $inputData->type === Arg::Optional
                ? new None()
                : new Some(SongType::from((int)$inputData->type)),
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
