<?php

declare(strict_types=1);

namespace Release\Application\Public\Query;

readonly class ReleaseTrackItem
{
    /**
     * @param list<ReleaseTrackSongItem> $songs 収録順。2 件以上ならメドレー
     */
    public function __construct(
        public int $trackNo,
        public ?string $title,
        public array $songs,
    ) {
    }
}
