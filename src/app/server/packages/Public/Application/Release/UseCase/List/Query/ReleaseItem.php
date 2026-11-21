<?php

declare(strict_types=1);

namespace Public\Application\Release\UseCase\List\Query;

use Release\Domain\Models\ReleaseFormat;

readonly class ReleaseItem
{
    /**
     * @param list<ReleaseFormat>     $formats
     * @param list<ReleaseMediumItem> $media
     */
    public function __construct(
        public string $releaseId,
        public string $name,
        public string $releasedOn,
        public string $description,
        public array $formats,
        public array $media,
    ) {
    }
}
