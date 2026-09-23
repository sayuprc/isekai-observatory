<?php

declare(strict_types=1);

namespace Event\Application\Admin\UseCase\Update;

readonly class UpdateInputData
{
    /**
     * @param array{startOn: ?string, endOn: ?string}                                                                                                                                          $schedule
     * @param list<string>                                                                                                                                                                     $venueIds
     * @param list<string>                                                                                                                                                                     $mediaIds
     * @param list<array{displayName: string, url: string, orderNo: int}>                                                                                                                      $sources
     * @param list<array{performanceId: string, songId: string, songTitle: string, orderNo: int, coVocalists: list<array{personId: string, name: string, creditName: ?string, orderNo: int}>}> $performances
     * @param list<array{setlistItemId: string, orderNo: int, label: ?string, performances: list<array{performanceId: string}>}>                                                               $setlist
     */
    public function __construct(
        public string $eventId,
        public string $title,
        public string $description,
        public int $typeValue,
        public array $schedule,
        public int $statusValue,
        public bool $isDisplay,
        public array $venueIds,
        public array $mediaIds,
        public array $sources,
        public array $performances,
        public array $setlist,
    ) {
    }
}
