<?php

declare(strict_types=1);

namespace Venue\Application\Admin\UseCase\Search;

use Support\Domain\SearchCriteria\Order;
use Support\Domain\SearchCriteria\PerPage;
use Support\Optional\Arg;
use Venue\Domain\Criteria\Sort;

readonly class SearchInputData
{
    public function __construct(
        public Arg|string $name = Arg::Optional,
        public Arg|int $kind = Arg::Optional,
        public Sort $sort = Sort::Name,
        public Order $order = Order::Asc,
        public int $page = 1,
        public PerPage $perPage = PerPage::TwentyFive,
    ) {
    }
}
