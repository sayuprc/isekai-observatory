<?php

declare(strict_types=1);

namespace Event\Application\Viewer\UseCase\List;

use Event\Application\Viewer\Query\EventQueryServiceInterface;

readonly class ListUseCase
{
    private const int DEFAULT_LIMIT = 50;

    private const int MAX_LIMIT = 50;

    public function __construct(private EventQueryServiceInterface $query)
    {
    }

    public function handle(ListInputData $inputData): ListOutputData
    {
        $page = $this->query->list(
            $inputData->cursor,
            min(self::MAX_LIMIT, $inputData->limit ?? self::DEFAULT_LIMIT),
        );

        return new ListOutputData($page->events, $page->nextCursor);
    }
}
