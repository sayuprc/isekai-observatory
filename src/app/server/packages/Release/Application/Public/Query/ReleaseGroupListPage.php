<?php

declare(strict_types=1);

namespace Release\Application\Public\Query;

readonly class ReleaseGroupListPage
{
    /**
     * @param list<ReleaseGroupListItem> $releaseGroups
     */
    public function __construct(
        public array $releaseGroups,
        public ?string $nextPageToken,
    ) {
    }
}
