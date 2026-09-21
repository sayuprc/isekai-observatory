<?php

declare(strict_types=1);

namespace Media\Application\Viewer\UseCase\List;

use Media\Application\Viewer\Query\MediaQueryServiceInterface;

readonly class ListUseCase
{
    private const DEFAULT_LIMIT = 50;

    private const MAX_LIMIT = 50;

    public function __construct(private MediaQueryServiceInterface $query)
    {
    }

    public function handle(ListInputData $inputData): ListOutputData
    {
        $page = $this->query->list(
            $inputData->cursor,
            min(self::MAX_LIMIT, $inputData->limit ?? self::DEFAULT_LIMIT),
        );

        return new ListOutputData($page->media, $page->nextCursor);
    }
}
