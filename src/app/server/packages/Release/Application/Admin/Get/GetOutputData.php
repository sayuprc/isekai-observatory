<?php

declare(strict_types=1);

namespace Release\Application\Admin\Get;

use Release\Application\Admin\Get\Query\ReleaseReferencedSong;
use Release\Domain\Models\Release;
use Release\Domain\Models\ReleaseGroup;

readonly class GetOutputData
{
    /**
     * @param array<int, ReleaseReferencedSong> $songs
     */
    public function __construct(
        public Release $release,
        public ReleaseGroup $releaseGroup,
        public array $songs,
    ) {
    }
}
