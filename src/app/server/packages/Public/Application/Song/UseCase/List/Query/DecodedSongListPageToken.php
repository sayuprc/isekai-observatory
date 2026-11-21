<?php

declare(strict_types=1);

namespace Public\Application\Song\UseCase\List\Query;

readonly class DecodedSongListPageToken
{
    public function __construct(
        public int $orderNo,
        public string $songId,
    ) {
    }
}
