<?php

declare(strict_types=1);

namespace Release\Application\Admin\UseCase\Create;

/**
 * @phpstan-type MediumInput array{position: int, name: ?string, tracks: list<array{songId: ?string, title: ?string, trackNo: int}>}
 */
readonly class CreateInputData
{
    /**
     * @param list<int>         $formatValues
     * @param list<MediumInput> $media
     */
    public function __construct(
        public string $releaseGroupId,
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
