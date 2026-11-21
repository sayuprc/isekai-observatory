<?php

declare(strict_types=1);

namespace Viewer\Application\Media\UseCase\List\Query;

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
