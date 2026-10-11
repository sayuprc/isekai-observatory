<?php

declare(strict_types=1);

namespace Release\Application\Viewer\Query;

readonly class ReleaseGroupListPage
{
    /**
     * @param array<ReleaseGroupListItem> $releaseGroups
     */
    public function __construct(
        public array $releaseGroups,
        public ?string $nextCursor,
    ) {
    }
}
