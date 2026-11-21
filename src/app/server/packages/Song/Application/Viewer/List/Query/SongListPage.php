<?php

declare(strict_types=1);

namespace Song\Application\Viewer\List\Query;

readonly class SongListPage
{
    /**
     * @param array<SongListItem> $songs
     */
    public function __construct(
        public array $songs,
        public ?string $nextCursor,
    ) {
    }
}
