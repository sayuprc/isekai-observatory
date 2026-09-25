<?php

declare(strict_types=1);

namespace Song\Application\Viewer\Query;

readonly class SongPerformanceHistory
{
    /**
     * @param array{startOn: ?string, endOn: ?string} $schedule
     * @param list<string>                            $coVocalistNames
     */
    public function __construct(
        public string $eventId,
        public string $eventTitle,
        public int $typeValue,
        public array $schedule,
        public array $coVocalistNames = [],
    ) {
    }
}
