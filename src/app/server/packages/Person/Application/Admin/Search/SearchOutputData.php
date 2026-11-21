<?php

declare(strict_types=1);

namespace Person\Application\Admin\Search;

use Person\Application\Admin\Search\Query\PersonUsageCount;
use Person\Domain\Models\Person;

readonly class SearchOutputData
{
    /**
     * @param array<Person>                   $persons
     * @param array<string, PersonUsageCount> $usageCounts 人物 ID (UUID) ごとの参照件数
     */
    public function __construct(
        public array $persons,
        public int $maxPage,
        public array $usageCounts,
    ) {
    }
}
