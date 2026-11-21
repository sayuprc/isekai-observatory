<?php

declare(strict_types=1);

namespace Media\Application\Viewer\List\Query;

use Song\Domain\Models\SongType;

readonly class MediaSongSummary
{
    public function __construct(
        public string $songId,
        public string $title,
        public SongType $type,
    ) {
    }
}
