<?php

declare(strict_types=1);

namespace Event\Application\Admin\Assemble;

readonly class AssembledPerformance
{
    /**
     * @param array<int, AssembledCoVocalist> $coVocalists
     */
    public function __construct(
        public string $performanceId,
        public string $songId,
        public string $songTitle,
        public int $orderNo,
        public array $coVocalists,
    ) {
    }
}
