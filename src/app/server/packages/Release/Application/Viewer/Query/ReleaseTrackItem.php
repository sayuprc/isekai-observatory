<?php

declare(strict_types=1);

namespace Release\Application\Viewer\Query;

readonly class ReleaseTrackItem
{
    /**
     * @param array<int, ReleaseTrackSong> $songs
     */
    public function __construct(
        public int $trackNo,
        public ?string $title,
        public array $songs,
    ) {
    }
}
