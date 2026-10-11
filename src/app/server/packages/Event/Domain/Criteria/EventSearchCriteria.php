<?php

declare(strict_types=1);

namespace Event\Domain\Criteria;

use Event\Domain\Models\EventStatus;
use Event\Domain\Models\EventType;
use Support\Domain\SearchCriteria\Order;
use Support\Domain\SearchCriteria\PerPage;
use Support\Domain\ValueObjects\String\TextNormalizer;
use Support\Optional\Optional;
use Support\Optional\Some;

readonly class EventSearchCriteria
{
    /** @var Optional<string> */
    public Optional $title;

    /**
     * @param Optional<string>      $title
     * @param Optional<EventType>   $type
     * @param Optional<EventStatus> $status
     * @param Optional<bool>        $isDisplay
     */
    public function __construct(
        Optional $title,
        public Optional $type,
        public Optional $status,
        public Optional $isDisplay,
        public Sort $sort = Sort::Schedule,
        public Order $order = Order::Asc,
        public int $page = 1,
        public PerPage $perPage = PerPage::TwentyFive,
    ) {
        // 保存側の EventTitle と対称に、検索語も NFC へ揃える
        $this->title = $title->isPresent()
            ? new Some(TextNormalizer::toNfc($title->get()))
            : $title;
    }
}
