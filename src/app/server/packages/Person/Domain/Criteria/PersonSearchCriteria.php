<?php

declare(strict_types=1);

namespace Person\Domain\Criteria;

use Support\Domain\SearchCriteria\Order;
use Support\Domain\SearchCriteria\PerPage;
use Support\Domain\ValueObjects\String\TextNormalizer;
use Support\Optional\Optional;
use Support\Optional\Some;

readonly class PersonSearchCriteria
{
    /** @var Optional<string> */
    public Optional $name;

    /**
     * @param Optional<string> $name
     */
    public function __construct(
        Optional $name,
        public Sort $sort = Sort::OrderNo,
        public Order $order = Order::Asc,
        public int $page = 1,
        public PerPage $perPage = PerPage::Fifty,
    ) {
        // 保存側の PersonName と対称に、検索語も NFC へ揃える
        $this->name = $name->isPresent()
            ? new Some(TextNormalizer::toNfc($name->get()))
            : $name;
    }
}
