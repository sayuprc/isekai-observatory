<?php

declare(strict_types=1);

namespace Release\Application\Viewer\Query;

use Release\Domain\Models\ReleaseFormat;

readonly class ReleaseListItem
{
    /**
     * @param array<ReleaseFormat>     $formats
     * @param array<ReleaseMediumItem> $media
     */
    public function __construct(
        public string $releaseId,
        public string $name,
        public string $releasedOn,
        public string $description,
        public string $color,
        public int $orderNo,
        public array $formats,
        public array $media,
    ) {
    }
}
