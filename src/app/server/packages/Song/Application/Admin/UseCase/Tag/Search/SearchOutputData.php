<?php

declare(strict_types=1);

namespace Song\Application\Admin\UseCase\Tag\Search;

use Song\Domain\Models\Tag\SongTag;

readonly class SearchOutputData
{
    /**
     * @param array<SongTag> $tags
     */
    public function __construct(
        public array $tags,
        public int $maxPage,
    ) {
    }
}
