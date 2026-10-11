<?php

declare(strict_types=1);

namespace Release\Application\Viewer\Query;

readonly class ReleaseMediumItem
{
    /**
     * @param array<ReleaseTrackItem> $tracks
     */
    public function __construct(
        public int $position,
        public ?string $name,
        public array $tracks,
    ) {
    }
}
