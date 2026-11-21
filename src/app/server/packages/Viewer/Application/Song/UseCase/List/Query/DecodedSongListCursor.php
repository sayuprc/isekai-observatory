<?php

declare(strict_types=1);

namespace Viewer\Application\Song\UseCase\List\Query;

readonly class DecodedSongListCursor
{
    public function __construct(
        public int $orderNo,
        public string $songId,
    ) {
    }
}
