<?php

declare(strict_types=1);

namespace Tag\Application\Public\Query;

use SensitiveParameter;

readonly class TagListPage
{
    /**
     * @param list<TagListItem> $tags
     */
    public function __construct(
        public array $tags,
        #[SensitiveParameter]
        public ?string $nextPageToken,
    ) {
    }
}
