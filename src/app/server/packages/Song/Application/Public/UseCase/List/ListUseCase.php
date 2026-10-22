<?php

declare(strict_types=1);

namespace Song\Application\Public\UseCase\List;

use Song\Application\Public\Query\SongQueryServiceInterface;

readonly class ListUseCase
{
    private const int DEFAULT_PAGE_SIZE = 100;

    public function __construct(private SongQueryServiceInterface $query)
    {
    }

    public function handle(ListInputData $inputData): ListOutputData
    {
        $page = $this->query->list($inputData->pageToken, $inputData->pageSize ?? self::DEFAULT_PAGE_SIZE);

        return new ListOutputData($page->songs, $page->nextPageToken);
    }
}
