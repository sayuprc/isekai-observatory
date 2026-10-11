<?php

declare(strict_types=1);

namespace Media\Application\Admin\UseCase\Search;

use Media\Application\Admin\Query\MediaUsageCount;
use Media\Domain\Models\Media;

readonly class SearchOutputData
{
    /**
     * @param list<Media>                    $media
     * @param array<string, MediaUsageCount> $usageCounts メディア ID (UUID) ごとの参照件数
     */
    public function __construct(
        public array $media,
        public int $maxPage,
        public array $usageCounts,
    ) {
    }
}
