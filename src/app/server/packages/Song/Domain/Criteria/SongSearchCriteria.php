<?php

declare(strict_types=1);

namespace Song\Domain\Criteria;

use Song\Domain\Models\SongType;
use Support\Domain\SearchCriteria\Order;
use Support\Domain\SearchCriteria\PerPage;
use Support\Domain\ValueObjects\String\TextNormalizer;
use Support\Optional\Optional;
use Support\Optional\Some;

readonly class SongSearchCriteria
{
    /** @var Optional<string> */
    public Optional $title;

    /**
     * @param Optional<string>   $title
     * @param Optional<SongType> $type
     * @param Optional<bool>     $isDisplay
     */
    public function __construct(
        Optional $title,
        public Optional $type,
        public Optional $isDisplay,
        public Sort $sort = Sort::OrderNo,
        public Order $order = Order::Asc,
        public int $page = 1,
        public PerPage $perPage = PerPage::Fifty,
    ) {
        // 保存側の Title と対称に、検索語も NFC へ揃える
        $this->title = $title->isPresent()
            ? new Some(TextNormalizer::toNfc($title->get()))
            : $title;
    }
}
