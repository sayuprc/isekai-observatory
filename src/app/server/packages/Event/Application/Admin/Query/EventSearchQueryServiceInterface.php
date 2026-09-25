<?php

declare(strict_types=1);

namespace Event\Application\Admin\Query;

use Event\Domain\Criteria\EventSearchCriteria;

interface EventSearchQueryServiceInterface
{
    /**
     * @return list<EventSummary>
     */
    public function search(EventSearchCriteria $criteria): array;

    public function maxPage(EventSearchCriteria $criteria): int;
}
