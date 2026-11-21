<?php

declare(strict_types=1);

namespace Public\Application\Venue\UseCase\List;

use Public\Application\Venue\UseCase\List\Query\VenueQueryServiceInterface;

readonly class ListUseCase
{
    private const int DEFAULT_PAGE_SIZE = 100;

    public function __construct(private VenueQueryServiceInterface $query)
    {
    }

    public function handle(ListInputData $inputData): ListOutputData
    {
        $page = $this->query->list($inputData->pageToken, $inputData->pageSize ?? self::DEFAULT_PAGE_SIZE);

        return new ListOutputData($page->venues, $page->nextPageToken);
    }
}
