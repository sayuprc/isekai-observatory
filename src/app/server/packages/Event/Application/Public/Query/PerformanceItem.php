<?php

declare(strict_types=1);

namespace Event\Application\Public\Query;

readonly class PerformanceItem
{
    /**
     * @param ?string              $songId      参照先の楽曲が非公開なら null
     * @param list<CoVocalistItem> $coVocalists
     */
    public function __construct(
        public string $performanceId,
        public ?string $songId,
        public string $songTitle,
        public array $coVocalists,
    ) {
    }
}
