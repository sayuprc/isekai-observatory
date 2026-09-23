<?php

declare(strict_types=1);

namespace Event\Domain\Criteria;

use Support\Domain\SearchCriteria\Order;
use Support\Domain\SearchCriteria\PerPage;

readonly class EventSearchCriteria
{
    public function __construct(
        public ?string $title,
        public ?int $type,
        public ?int $status,
        public ?bool $isDisplay,
        public string $sort,
        public Order $order,
        public int $page,
        public PerPage $perPage,
    ) {
    }
}
