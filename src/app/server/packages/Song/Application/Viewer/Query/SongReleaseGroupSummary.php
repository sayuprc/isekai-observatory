<?php

declare(strict_types=1);

namespace Song\Application\Viewer\Query;

readonly class SongReleaseGroupSummary
{
    public function __construct(
        public string $releaseGroupId,
        public string $title,
        public int $typeValue,
        public string $firstReleasedOn,
        public string $color,
    ) {
    }
}
