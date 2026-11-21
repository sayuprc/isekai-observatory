<?php

declare(strict_types=1);

namespace Song\Application\Admin\UseCase\Search\Query;

use Song\Domain\Criteria\SongSearchCriteria;

interface SongQueryServiceInterface
{
    /**
     * @return array<SongSummary>
     */
    public function search(SongSearchCriteria $criteria): array;

    public function maxPage(SongSearchCriteria $criteria): int;
}
