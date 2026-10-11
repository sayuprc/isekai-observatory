<?php

declare(strict_types=1);

namespace Person\Application\Admin\UseCase\Group\Search;

use Support\Domain\SearchCriteria\PerPage;
use Support\Optional\Arg;

readonly class SearchInputData
{
    public function __construct(
        public Arg|string $name = Arg::Optional,
        public int $page = 1,
        public PerPage $perPage = PerPage::Fifty,
    ) {
    }
}
