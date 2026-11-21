<?php

declare(strict_types=1);

namespace Release\Application\Admin\Search;

use Release\Application\Admin\Search\Query\ReleaseSummary;

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
