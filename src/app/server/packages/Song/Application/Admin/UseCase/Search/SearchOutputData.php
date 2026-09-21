<?php

declare(strict_types=1);

namespace Song\Application\Admin\UseCase\Search;

use Song\Application\Admin\Query\SongSummary;

readonly class SearchOutputData
{
    /**
     * @param array<SongSummary> $songs
     */
    public function __construct(
        public array $songs,
        public int $maxPage,
    ) {
    }
}
