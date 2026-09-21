<?php

declare(strict_types=1);

namespace Venue\Domain\Criteria;

use Support\Domain\SearchCriteria\Order;
use Support\Domain\SearchCriteria\PerPage;
use Support\Domain\ValueObjects\String\TextNormalizer;
use Support\Optional\Optional;
use Support\Optional\Some;
use Venue\Domain\Models\VenueKind;

readonly class VenueSearchCriteria
{
    /** @var Optional<string> */
    public Optional $name;

    /**
     * @param Optional<string>    $name
     * @param Optional<VenueKind> $kind
     */
    public function __construct(
        Optional $name,
        public Optional $kind,
        public Sort $sort = Sort::Name,
        public Order $order = Order::Asc,
        public int $page = 1,
        public PerPage $perPage = PerPage::TwentyFive,
    ) {
        $this->name = $name->isPresent()
            ? new Some(TextNormalizer::toNfc(mb_trim($name->get())))
            : $name;
    }
}
