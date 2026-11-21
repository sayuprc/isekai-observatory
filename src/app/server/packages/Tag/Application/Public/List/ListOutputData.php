<?php

declare(strict_types=1);

namespace Tag\Application\Public\List;

use SensitiveParameter;
use Tag\Application\Public\List\Query\TagListItem;

readonly class ListOutputData
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
