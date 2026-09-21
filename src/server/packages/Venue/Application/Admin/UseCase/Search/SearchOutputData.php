<?php

declare(strict_types=1);

namespace Venue\Application\Admin\UseCase\Search;

use Venue\Domain\Models\Venue;

readonly class SearchOutputData
{
    /**
     * @param array<Venue> $venues
     */
    public function __construct(
        public array $venues,
        public int $maxPage,
    ) {
    }
}
