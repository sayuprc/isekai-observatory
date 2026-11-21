<?php

declare(strict_types=1);

namespace Public\Application\Person\UseCase\Group\List;

use Public\Application\Person\UseCase\Group\List\Query\PersonGroupQueryServiceInterface;

readonly class ListUseCase
{
    private const int DEFAULT_PAGE_SIZE = 100;

    public function __construct(private PersonGroupQueryServiceInterface $query)
    {
    }

    public function handle(ListInputData $inputData): ListOutputData
    {
        $page = $this->query->list($inputData->pageToken, $inputData->pageSize ?? self::DEFAULT_PAGE_SIZE);

        return new ListOutputData($page->personGroups, $page->nextPageToken);
    }
}
