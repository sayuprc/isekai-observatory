<?php

declare(strict_types=1);

namespace Release\Application\Public\Query;

readonly class ReleaseMediumItem
{
    /**
     * @param list<ReleaseTrackItem> $tracks
     */
    public function __construct(
        public int $position,
        public ?string $name,
        public array $tracks,
    ) {
    }
}
