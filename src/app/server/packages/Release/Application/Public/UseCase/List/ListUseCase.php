<?php

declare(strict_types=1);

namespace Release\Application\Public\UseCase\List;

use Release\Application\Public\Query\ReleaseGroupQueryServiceInterface;

readonly class ListUseCase
{
    private const int DEFAULT_PAGE_SIZE = 100;

    public function __construct(private ReleaseGroupQueryServiceInterface $query)
    {
    }

    public function handle(ListInputData $inputData): ListOutputData
    {
        $page = $this->query->list($inputData->pageToken, $inputData->pageSize ?? self::DEFAULT_PAGE_SIZE);

        return new ListOutputData($page->releaseGroups, $page->nextPageToken);
    }
}
