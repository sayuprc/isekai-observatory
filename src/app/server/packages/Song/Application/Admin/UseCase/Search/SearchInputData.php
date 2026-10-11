<?php

declare(strict_types=1);

namespace Song\Application\Admin\UseCase\Search;

use Song\Domain\Criteria\Sort;
use Support\Domain\SearchCriteria\Order;
use Support\Domain\SearchCriteria\PerPage;
use Support\Optional\Arg;

readonly class SearchInputData
{
    public function __construct(
        public Arg|string $title = Arg::Optional,
        public Arg|int|string $type = Arg::Optional,
        public Arg|bool $isDisplay = Arg::Optional,
        public Sort $sort = Sort::OrderNo,
        public Order $order = Order::Asc,
        public int $page = 1,
        public PerPage $perPage = PerPage::TwentyFive,
    ) {
    }
}
