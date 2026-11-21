<?php

declare(strict_types=1);

namespace Release\Application\Admin\UseCase\Search;

use Release\Application\Admin\UseCase\Search\Query\ReleaseSummary;

readonly class SearchOutputData
{
    /**
     * @param array<int, ReleaseSummary> $releases
     */
    public function __construct(
        public array $releases,
        public int $maxPage,
    ) {
    }
}
