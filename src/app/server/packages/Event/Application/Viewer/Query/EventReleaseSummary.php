<?php

declare(strict_types=1);

namespace Event\Application\Viewer\Query;

use Release\Domain\Models\ReleaseFormat;

readonly class EventReleaseSummary
{
    /**
     * @param list<ReleaseFormat> $formats
     */
    public function __construct(
        public string $releaseId,
        public string $releaseGroupId,
        public string $releaseGroupTitle,
        public string $name,
        public string $releasedOn,
        public array $formats,
    ) {
    }
}
