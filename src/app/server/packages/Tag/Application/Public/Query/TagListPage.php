<?php

declare(strict_types=1);

namespace Tag\Application\Public\Query;

readonly class TagListPage
{
    /**
     * @param list<TagListItem> $tags
     */
    public function __construct(
        public array $tags,
        public ?string $nextPageToken,
    ) {
    }
}
