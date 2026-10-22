<?php

declare(strict_types=1);

namespace Song\Application\Public\Query;

readonly class DecodedSongListPageToken
{
    public function __construct(
        public int $orderNo,
        public string $songId,
    ) {
    }
}
