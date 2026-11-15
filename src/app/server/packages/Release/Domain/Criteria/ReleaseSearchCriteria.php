<?php

declare(strict_types=1);

namespace Release\Domain\Criteria;

use Support\Domain\SearchCriteria\PerPage;
use Support\Domain\ValueObjects\String\TextNormalizer;
use Support\Optional\Optional;
use Support\Optional\Some;

readonly class ReleaseSearchCriteria
{
    /** @var Optional<string> */
    public Optional $releaseGroupTitle;

    /**
     * @param Optional<string> $releaseGroupTitle
     */
    public function __construct(
        Optional $releaseGroupTitle,
        public int $page = 1,
        public PerPage $perPage = PerPage::TwentyFive,
    ) {
        // 保存側の ReleaseGroupTitle と対称に、検索語も NFC へ揃える
        $this->releaseGroupTitle = $releaseGroupTitle->isPresent()
            ? new Some(TextNormalizer::toNfc($releaseGroupTitle->get()))
            : $releaseGroupTitle;
    }
}
