<?php

declare(strict_types=1);

namespace Release\Application\Admin\Group\Search;

use Release\Application\Admin\Group\Search\Query\ReleaseGroupSummary;

readonly class SearchOutputData
{
    /**
     * @param array<int, ReleaseGroupSummary> $releaseGroups
     */
    public function __construct(
        public array $releaseGroups,
        public int $maxPage,
    ) {
    }
}
