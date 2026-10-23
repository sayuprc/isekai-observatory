<?php

declare(strict_types=1);

namespace Person\Application\Public\UseCase\List;

use Person\Application\Public\Query\PersonQueryServiceInterface;

readonly class ListUseCase
{
    private const int DEFAULT_PAGE_SIZE = 100;

    public function __construct(private PersonQueryServiceInterface $query)
    {
    }

    public function handle(ListInputData $inputData): ListOutputData
    {
        $page = $this->query->list($inputData->pageToken, $inputData->pageSize ?? self::DEFAULT_PAGE_SIZE);

        return new ListOutputData($page->persons, $page->nextPageToken);
    }
}
