<?php

declare(strict_types=1);

namespace Song\Application\Admin\Assemble;

readonly class AssembledSong
{
    /**
     * @param array<int, AssembledPerson> $persons
     * @param array<int, AssembledTag>    $tags
     * @param array<int, AssembledMedia>  $media
     */
    public function __construct(
        public string $songId,
        public string $title,
        public string $description,
        public ?string $lyricsLink,
        public string $typeName,
        public int $typeValue,
        public bool $isDisplay,
        public int $orderNo,
        public array $persons,
        public array $tags = [],
        public array $media = [],
    ) {
    }
}
