<?php

declare(strict_types=1);

namespace Media\Domain\Criteria;

use Media\Domain\Models\MediaType;
use Support\Domain\SearchCriteria\Order;
use Support\Domain\SearchCriteria\PerPage;
use Support\Domain\ValueObjects\String\TextNormalizer;
use Support\Optional\Optional;
use Support\Optional\Some;

readonly class MediaSearchCriteria
{
    /** @var Optional<string> */
    public Optional $title;

    /**
     * @param Optional<string>    $title
     * @param Optional<MediaType> $type
     * @param Optional<bool>      $isDisplay
     */
    public function __construct(
        Optional $title,
        public Optional $type,
        public Optional $isDisplay,
        public Sort $sort = Sort::PublishedAt,
        public Order $order = Order::Asc,
        public int $page = 1,
        public PerPage $perPage = PerPage::TwentyFive,
    ) {
        // 保存側の MediaTitle と対称に、検索語も NFC へ揃える
        // これにより「検索タイトルは常に NFC」を不変条件として保証する
        $this->title = $title->isPresent()
            ? new Some(TextNormalizer::toNfc($title->get()))
            : $title;
    }
}
