<?php

declare(strict_types=1);

namespace Song\Application\Admin\Query;

use Song\Domain\Models\SongType;

readonly class SongSummary
{
    public function __construct(
        public string $songId,
        public string $title,
        public SongType $type,
        public bool $isDisplay,
        public int $orderNo,
    ) {
    }
}
