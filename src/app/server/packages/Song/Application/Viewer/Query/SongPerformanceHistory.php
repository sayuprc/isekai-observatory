<?php

declare(strict_types=1);

namespace Song\Application\Viewer\Query;

readonly class SongPerformanceHistory
{
    /** @param array{startOn: ?string, endOn: ?string} $schedule */
    public function __construct(
        public string $eventId,
        public string $eventTitle,
        public int $typeValue,
        public array $schedule,
        /** @var list<string> */
        public array $coVocalistNames = [],
    ) {
    }
}
