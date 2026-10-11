<?php

declare(strict_types=1);

namespace Song\Application\Admin\UseCase\Tag\Search;

use Song\Domain\Models\Tag\SongTag;

readonly class SearchOutputData
{
    /**
     * @param array<SongTag>     $tags
     * @param array<string, int> $usageCounts 楽曲タグ ID (UUID) ごとの、付いている楽曲の件数
     */
    public function __construct(
        public array $tags,
        public int $maxPage,
        public array $usageCounts,
    ) {
    }
}
