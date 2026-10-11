<?php

declare(strict_types=1);

namespace Song\Application\Viewer\Query;

use Song\Domain\Models\SongType;

readonly class SongListItem
{
    /**
     * @param array<string>                  $lyricists
     * @param array<string>                  $composers
     * @param array<string>                  $arrangers
     * @param array<SongMediaSummary>        $media
     * @param array<SongReleaseGroupSummary> $releaseGroups
     * @param array<SongPerformanceHistory>  $performances
     */
    public function __construct(
        public string $songId,
        public string $title,
        public SongType $type,
        public string $description,
        public array $lyricists,
        public array $composers,
        public array $arrangers,
        public array $media,
        public array $releaseGroups,
        public array $performances,
        public int $orderNo,
    ) {
    }
}
