<?php

declare(strict_types=1);

namespace Event\Application\Admin\UseCase\Search;

use Support\Domain\SearchCriteria\Order;
use Support\Domain\SearchCriteria\PerPage;

readonly class SearchInputData
{
    public function __construct(
        public ?string $title = null,
        public ?int $type = null,
        public ?int $status = null,
        public ?bool $isDisplay = null,
        public string $sort = 'schedule',
        public Order $order = Order::Asc,
        public int $page = 1,
        public PerPage $perPage = PerPage::TwentyFive,
    ) {
    }
}
