<?php

declare(strict_types=1);

namespace Release\Application\Viewer\Query;

use Release\Domain\Models\ReleaseGroupType;

readonly class ReleaseGroupListItem
{
    /**
     * @param array<ReleaseListItem> $releases
     */
    public function __construct(
        public string $releaseGroupId,
        public string $title,
        public ReleaseGroupType $type,
        public string $description,
        public string $firstReleasedOn,
        public array $releases,
    ) {
    }
}
