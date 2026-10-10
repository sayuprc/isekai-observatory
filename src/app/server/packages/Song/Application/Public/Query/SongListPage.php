<?php

declare(strict_types=1);

namespace Song\Application\Public\Query;

readonly class SongListPage
{
    /**
     * @param list<SongListItem> $songs
     */
    public function __construct(
        public array $songs,
        public ?string $nextPageToken,
    ) {
    }
}
