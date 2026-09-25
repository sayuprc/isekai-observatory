<?php

declare(strict_types=1);

namespace Event\Application\Viewer\Query;

readonly class EventPerformanceSummary
{
    /**
     * @param ?string                       $songId      非公開の楽曲なら null
     * @param array<EventCoVocalistSummary> $coVocalists
     */
    public function __construct(
        public string $performanceId,
        public ?string $songId,
        public string $songTitle,
        public array $coVocalists,
    ) {
    }
}
