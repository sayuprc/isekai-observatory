<?php

declare(strict_types=1);

namespace Release\Application\Admin\UseCase\Group\Search;

use Release\Application\Admin\Query\ReleaseGroupSummary;

readonly class SearchOutputData
{
    /**
     * @param list<ReleaseGroupSummary> $releaseGroups
     */
    public function __construct(
        public array $releaseGroups,
        public int $maxPage,
    ) {
    }
}
