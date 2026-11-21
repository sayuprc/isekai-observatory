<?php

declare(strict_types=1);

namespace Public\Application\Tag\UseCase\List\Query;

readonly class DecodedTagListPageToken
{
    public function __construct(
        public int $orderNo,
        public string $tagId,
    ) {
    }
}
