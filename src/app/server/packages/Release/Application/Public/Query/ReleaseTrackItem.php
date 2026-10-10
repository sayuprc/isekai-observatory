<?php

declare(strict_types=1);

namespace Release\Application\Public\Query;

readonly class ReleaseTrackItem
{
    /**
     * @param ?string $songId 楽曲を参照しないトラックと、参照先の楽曲が非公開のトラックは null
     */
    public function __construct(
        public int $trackNo,
        public ?string $songId,
        public string $title,
    ) {
    }
}
