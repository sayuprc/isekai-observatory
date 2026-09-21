<?php

declare(strict_types=1);

namespace Release\Application\Admin\UseCase\Get;

use Release\Application\Admin\Query\ReleaseReferencedSong;
use Release\Domain\Models\Release;
use Release\Domain\Models\ReleaseGroup;

readonly class GetOutputData
{
    /**
     * @param list<ReleaseReferencedSong> $songs
     */
    public function __construct(
        public Release $release,
        public ReleaseGroup $releaseGroup,
        public array $songs,
    ) {
    }
}
