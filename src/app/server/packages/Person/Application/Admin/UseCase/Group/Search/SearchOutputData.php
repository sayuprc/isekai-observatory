<?php

declare(strict_types=1);

namespace Person\Application\Admin\UseCase\Group\Search;

use Person\Application\Admin\Query\PersonGroupSummary;

readonly class SearchOutputData
{
    /**
     * @param list<PersonGroupSummary> $personGroups
     */
    public function __construct(
        public array $personGroups,
        public int $maxPage,
    ) {
    }
}
