<?php

declare(strict_types=1);

namespace Tag\Application\Public\Query;

readonly class TagListItem
{
    public function __construct(
        public string $tagId,
        public string $name,
    ) {
    }
}
