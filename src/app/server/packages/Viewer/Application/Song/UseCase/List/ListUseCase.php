<?php

declare(strict_types=1);

namespace Viewer\Application\Song\UseCase\List;

use Viewer\Application\Song\UseCase\List\Query\SongQueryServiceInterface;

readonly class ListUseCase
{
    private const int DEFAULT_LIMIT = 50;

    private const int MAX_LIMIT = 50;

    public function __construct(private SongQueryServiceInterface $query)
    {
    }

    public function handle(ListInputData $inputData): ListOutputData
    {
        $page = $this->query->list(
            $inputData->cursor,
            min(self::MAX_LIMIT, $inputData->limit ?? self::DEFAULT_LIMIT),
        );

        return new ListOutputData($page->songs, $page->nextCursor);
    }
}
