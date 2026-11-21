<?php

declare(strict_types=1);

namespace Public\Application\Tag\UseCase\List;

use Public\Application\Tag\UseCase\List\Query\TagListItem;
use SensitiveParameter;

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
