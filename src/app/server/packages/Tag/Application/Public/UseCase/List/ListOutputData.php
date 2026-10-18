<?php

declare(strict_types=1);

namespace Tag\Application\Public\UseCase\List;

use Tag\Application\Public\Query\TagListItem;

readonly class ListOutputData
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
