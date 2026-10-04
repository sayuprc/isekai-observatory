<?php

declare(strict_types=1);

namespace Venue\Application\Admin\UseCase\Search;

use Venue\Domain\Models\Venue;

readonly class SearchOutputData
{
    /**
     * @param array<Venue>       $venues
     * @param array<string, int> $usageCounts 開催先 ID (UUID) ごとの、使っているイベントの件数
     */
    public function __construct(
        public array $venues,
        public int $maxPage,
        public array $usageCounts,
    ) {
    }
}
