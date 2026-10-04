<?php

declare(strict_types=1);

namespace Person\Application\Admin\Query;

use Person\Domain\Criteria\PersonGroupSearchCriteria;
use Person\Domain\Models\PersonGroupId;

interface PersonGroupQueryServiceInterface
{
    /**
     * 名前の昇順で返す
     *
     * @return list<PersonGroupSummary>
     */
    public function search(PersonGroupSearchCriteria $criteria): array;

    public function maxPage(PersonGroupSearchCriteria $criteria): int;

    public function find(PersonGroupId $personGroupId): ?PersonGroupSummary;
}
