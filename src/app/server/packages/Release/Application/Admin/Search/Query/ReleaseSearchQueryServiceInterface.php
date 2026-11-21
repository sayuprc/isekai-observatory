<?php

declare(strict_types=1);

namespace Release\Application\Admin\Search\Query;

use Release\Domain\Criteria\ReleaseSearchCriteria;

interface ReleaseSearchQueryServiceInterface
{
    /**
     * @return array<int, ReleaseSummary>
     */
    public function search(ReleaseSearchCriteria $criteria): array;

    public function maxPage(ReleaseSearchCriteria $criteria): int;
}
