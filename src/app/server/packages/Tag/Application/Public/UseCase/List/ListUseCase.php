<?php

declare(strict_types=1);

namespace Tag\Application\Public\UseCase\List;

use Tag\Application\Public\Query\TagQueryServiceInterface;

readonly class ListUseCase
{
    private const int DEFAULT_PAGE_SIZE = 100;

    public function __construct(private TagQueryServiceInterface $query)
    {
    }

    public function handle(ListInputData $inputData): ListOutputData
    {
        $page = $this->query->list($inputData->pageToken, $inputData->pageSize ?? self::DEFAULT_PAGE_SIZE);

        return new ListOutputData($page->tags, $page->nextPageToken);
    }
}
