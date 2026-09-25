<?php

declare(strict_types=1);

namespace Event\Application\Admin\UseCase\Update;

/**
 * @phpstan-import-type SongPerformanceInput from \Event\Domain\Models\Performances\SongPerformances
 * @phpstan-import-type SetlistItemInput from \Event\Domain\Models\Setlist\Setlist
 * @phpstan-import-type EventSourceInput from \Event\Domain\Models\Sources\EventSources
 */
readonly class UpdateInputData
{
    /**
     * @param array{startOn: ?string, endOn: ?string}    $schedule
     * @param list<array{venueId: string, orderNo: int}> $venues
     * @param list<array{mediaId: string, orderNo: int}> $media
     * @param list<EventSourceInput>                     $sources
     * @param list<SongPerformanceInput>                 $performances
     * @param list<SetlistItemInput>                     $setlist
     */
    public function __construct(
        public string $eventId,
        public string $title,
        public string $description,
        public int $typeValue,
        public array $schedule,
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
