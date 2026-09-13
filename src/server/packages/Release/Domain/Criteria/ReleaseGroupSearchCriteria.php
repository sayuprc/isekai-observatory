<?php

declare(strict_types=1);

namespace Release\Domain\Criteria;

use Release\Domain\Models\ReleaseGroupType;
use Support\Domain\SearchCriteria\Order;
use Support\Domain\SearchCriteria\PerPage;
use Support\Domain\ValueObjects\String\TextNormalizer;
use Support\Optional\Optional;
use Support\Optional\Some;

readonly class ReleaseGroupSearchCriteria
{
    /** @var Optional<string> */
    public Optional $title;

    /**
     * @param Optional<string>           $title
     * @param Optional<ReleaseGroupType> $type
     * @param Optional<bool>             $isDisplay
     */
    public function __construct(
        Optional $title,
        public Optional $type,
        public Optional $isDisplay,
        public Sort $sort = Sort::FirstReleasedOn,
        public Order $order = Order::Desc,
        public int $page = 1,
        public PerPage $perPage = PerPage::TwentyFive,
    ) {
        // 保存側の ReleaseGroupTitle と対称に、検索語も NFC へ揃える
        $this->title = $title->isPresent()
            ? new Some(TextNormalizer::toNfc($title->get()))
            : $title;
    }
}
