<?php

declare(strict_types=1);

namespace Song\Application\Viewer\List\Query;

readonly class DecodedSongListCursor
{
    public function __construct(
        public int $orderNo,
        public string $songId,
    ) {
    }
}
