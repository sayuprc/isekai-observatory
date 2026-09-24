<?php

declare(strict_types=1);

namespace Event\Application\Admin\Assemble;

readonly class AssembledEvent
{
    /**
     * @param array<int, AssembledVenue>       $venues
     * @param array<int, AssembledMedia>       $media
     * @param array<int, AssembledSource>      $sources
     * @param array<int, AssembledPerformance> $performances
     * @param array<int, AssembledSetlistItem> $setlist
     */
    public function __construct(
        public string $eventId,
        public string $title,
        public string $description,
        public int $typeValue,
        public ?string $startOn,
        public ?string $endOn,
        public int $statusValue,
        public bool $isDisplay,
        public array $venues,
        public array $media,
        public array $sources,
        public array $performances,
        public array $setlist,
    ) {
    }
}
