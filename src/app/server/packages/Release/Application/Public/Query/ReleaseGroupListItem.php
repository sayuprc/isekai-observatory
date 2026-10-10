<?php

declare(strict_types=1);

namespace Release\Application\Public\Query;

use Release\Domain\Models\ReleaseGroupType;

readonly class ReleaseGroupListItem
{
    /**
     * @param list<ReleaseItem> $releases
     */
    public function __construct(
        public string $releaseGroupId,
        public string $title,
        public ReleaseGroupType $type,
        public string $description,
        public array $releases,
    ) {
    }
}
