<?php

declare(strict_types=1);

namespace Tag\Application\Admin\UseCase\Search;

use Support\Domain\SearchCriteria\Order;
use Support\Domain\SearchCriteria\PerPage;
use Support\Optional\Arg;
use Tag\Domain\Criteria\Sort;

readonly class SearchInputData
{
    public function __construct(
        public Arg|string $name = Arg::Optional,
        public Sort $sort = Sort::OrderNo,
        public Order $order = Order::Asc,
        public int $page = 1,
        public PerPage $perPage = PerPage::Fifty,
    ) {
    }
}
