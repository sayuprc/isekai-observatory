<?php

declare(strict_types=1);

namespace Release\Application\Viewer\Query;

readonly class ReleaseTrackSong
{
    public function __construct(
        public string $songId,
        public string $title,
        public bool $isDisplay,
    ) {
    }
}
