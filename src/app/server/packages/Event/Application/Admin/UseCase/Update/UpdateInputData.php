<?php

declare(strict_types=1);

namespace Event\Application\Admin\UseCase\Update;

/**
 * @phpstan-import-type _songPerformanceInput from \Event\Domain\Models\Performances\SongPerformances
 * @phpstan-import-type _setlistItemInput from \Event\Domain\Models\Setlist\Setlist
 * @phpstan-import-type _eventSourceInput from \Event\Domain\Models\Sources\EventSources
 */
readonly class UpdateInputData
{
    /**
     * @param array{startOn: ?string, endOn: ?string}    $schedule
     * @param list<array{venueId: string, orderNo: int}> $venues
     * @param list<array{mediaId: string, orderNo: int}> $media
     * @param list<_eventSourceInput>                    $sources
     * @param list<_songPerformanceInput>                $performances
     * @param list<_setlistItemInput>                    $setlist
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
