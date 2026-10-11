<?php

declare(strict_types=1);

namespace Release\Application\Viewer\Query;

readonly class ReleaseTrackItem
{
    public function __construct(
        public int $trackNo,
        public ?string $songId,
        public string $title,
        public bool $isDisplay,
    ) {
    }
}
