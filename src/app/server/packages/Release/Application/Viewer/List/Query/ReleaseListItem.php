<?php

declare(strict_types=1);

namespace Release\Application\Viewer\List\Query;

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
        public int $orderNo,
        public array $formats,
        public array $media,
    ) {
    }
}
