<?php

declare(strict_types=1);

namespace Song\Application\Public\Query;

use Song\Domain\Models\SongType;

readonly class SongListItem
{
    /**
     * @param list<SongCredit> $credits
     * @param list<string>     $mediaIds
     * @param list<string>     $tagIds
     */
    public function __construct(
        public string $songId,
        public string $title,
        public string $description,
        public SongType $type,
        public array $credits,
        public array $mediaIds,
        public array $tagIds,
    ) {
    }
}
