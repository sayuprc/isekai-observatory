<?php

declare(strict_types=1);

namespace Release\Application\Viewer\UseCase\List;

use Release\Application\Viewer\Query\ReleaseGroupQueryServiceInterface;

readonly class ListUseCase
{
    private const DEFAULT_LIMIT = 50;

    private const MAX_LIMIT = 50;

    public function __construct(private ReleaseGroupQueryServiceInterface $query)
    {
    }

    public function handle(ListInputData $inputData): ListOutputData
    {
        $page = $this->query->list(
            $inputData->cursor,
            min(self::MAX_LIMIT, $inputData->limit ?? self::DEFAULT_LIMIT),
        );

        return new ListOutputData($page->releaseGroups, $page->nextCursor);
    }
}
