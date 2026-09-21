<?php

declare(strict_types=1);

namespace Song\Application\Admin\UseCase\Update;

readonly class UpdateInputData
{
    /**
     * @param list<array{songTagId: string}>                         $tags
     * @param list<array{personId: string, role: int, orderNo: int}> $persons
     * @param list<array{mediaId: string, orderNo: int}>             $media
     */
    public function __construct(
        public string $songId,
        public string $title,
        public string $description,
        public ?string $lyricsLink,
        public int $typeValue,
        public bool $isDisplay,
        public int $orderNo,
        public array $tags,
        public array $persons,
        public array $media = [],
    ) {
    }
}
