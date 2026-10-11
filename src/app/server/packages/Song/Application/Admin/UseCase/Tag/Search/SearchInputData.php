<?php

declare(strict_types=1);

namespace Song\Application\Admin\UseCase\Tag\Search;

use Song\Domain\Criteria\Tag\SongTagSort;
use Support\Domain\SearchCriteria\Order;
use Support\Domain\SearchCriteria\PerPage;
use Support\Optional\Arg;

readonly class SearchInputData
{
    public function __construct(
        public Arg|string $name = Arg::Optional,
        public SongTagSort $sort = SongTagSort::OrderNo,
        public Order $order = Order::Asc,
        public int $page = 1,
        public PerPage $perPage = PerPage::Fifty,
    ) {
    }
}
