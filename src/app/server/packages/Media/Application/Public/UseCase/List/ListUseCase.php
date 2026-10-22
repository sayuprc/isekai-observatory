<?php

declare(strict_types=1);

namespace Media\Application\Public\UseCase\List;

use Media\Application\Public\Query\MediaQueryServiceInterface;

readonly class ListUseCase
{
    private const int DEFAULT_PAGE_SIZE = 100;

    public function __construct(private MediaQueryServiceInterface $query)
    {
    }

    public function handle(ListInputData $inputData): ListOutputData
    {
        $page = $this->query->list($inputData->pageToken, $inputData->pageSize ?? self::DEFAULT_PAGE_SIZE);

        return new ListOutputData($page->media, $page->nextPageToken);
    }
}
