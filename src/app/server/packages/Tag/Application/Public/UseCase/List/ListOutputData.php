<?php

declare(strict_types=1);

namespace Tag\Application\Public\UseCase\List;

use SensitiveParameter;
use Tag\Application\Public\Query\TagListItem;

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
