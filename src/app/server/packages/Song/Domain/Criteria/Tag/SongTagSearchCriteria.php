<?php

declare(strict_types=1);

namespace Song\Domain\Criteria\Tag;

use Support\Domain\SearchCriteria\Order;
use Support\Domain\SearchCriteria\PerPage;
use Support\Domain\ValueObjects\String\TextNormalizer;
use Support\Optional\Optional;
use Support\Optional\Some;

readonly class SongTagSearchCriteria
{
    /** @var Optional<string> */
    public Optional $name;

    /**
     * @param Optional<string> $name
     */
    public function __construct(
        Optional $name,
        public SongTagSort $sort = SongTagSort::OrderNo,
        public Order $order = Order::Asc,
        public int $page = 1,
        public PerPage $perPage = PerPage::Fifty,
    ) {
        // 保存側の SongTagName と対称に、検索語も NFC へ揃える
        $this->name = $name->isPresent()
            ? new Some(TextNormalizer::toNfc($name->get()))
            : $name;
    }
}
