<?php

declare(strict_types=1);

namespace Tag\Application\Admin\UseCase\Search;

use Tag\Application\Admin\Query\TagUsageCount;
use Tag\Domain\Models\Tag;

readonly class SearchOutputData
{
    /**
     * @param array<Tag>                   $tags
     * @param array<string, TagUsageCount> $usageCounts タグ ID (UUID) ごとの、付いている記録の件数
     */
    public function __construct(
        public array $tags,
        public int $maxPage,
        public array $usageCounts,
    ) {
    }
}
