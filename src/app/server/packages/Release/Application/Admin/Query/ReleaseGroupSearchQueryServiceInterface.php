<?php

declare(strict_types=1);

namespace Release\Application\Admin\Query;

use Release\Domain\Criteria\ReleaseGroupSearchCriteria;

interface ReleaseGroupSearchQueryServiceInterface
{
    /**
     * @return list<ReleaseGroupSummary>
     */
    public function search(ReleaseGroupSearchCriteria $criteria): array;

    public function maxPage(ReleaseGroupSearchCriteria $criteria): int;
}
