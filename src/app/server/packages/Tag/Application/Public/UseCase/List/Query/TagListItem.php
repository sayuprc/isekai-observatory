<?php

declare(strict_types=1);

namespace Tag\Application\Public\UseCase\List\Query;

readonly class TagListItem
{
    public function __construct(
        public string $tagId,
        public string $name,
    ) {
    }
}
