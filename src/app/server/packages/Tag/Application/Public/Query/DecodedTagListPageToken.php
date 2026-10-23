<?php

declare(strict_types=1);

namespace Tag\Application\Public\Query;

readonly class DecodedTagListPageToken
{
    public function __construct(
        public int $orderNo,
        public string $tagId,
    ) {
    }
}
