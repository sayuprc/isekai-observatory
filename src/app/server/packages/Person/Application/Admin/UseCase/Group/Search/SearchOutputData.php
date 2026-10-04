<?php

declare(strict_types=1);

namespace Person\Application\Admin\UseCase\Group\Search;

use Person\Application\Admin\Query\PersonGroupSummary;

readonly class SearchOutputData
{
    /**
     * @param list<PersonGroupSummary> $personGroups
     * @param array<string, int>       $usageCounts  人物グループ ID (UUID) ごとの、共演が記録されている楽曲披露の件数
     */
    public function __construct(
        public array $personGroups,
        public int $maxPage,
        public array $usageCounts,
    ) {
    }
}
