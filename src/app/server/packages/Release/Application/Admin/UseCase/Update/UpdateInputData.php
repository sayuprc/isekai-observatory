<?php

declare(strict_types=1);

namespace Release\Application\Admin\UseCase\Update;

/**
 * @phpstan-type _mediumInput array{position: int, name: ?string, tracks: list<array{songId: ?string, title: ?string, trackNo: int}>}
 */
readonly class UpdateInputData
{
    /**
     * @param list<int>          $formatValues
     * @param list<_mediumInput> $media
     */
    public function __construct(
        public string $releaseId,
        public string $name,
        public string $releasedOn,
        public string $description,
        public string $color,
        public bool $isDisplay,
        public int $orderNo,
        public array $formatValues,
        public array $media,
    ) {
    }
}
