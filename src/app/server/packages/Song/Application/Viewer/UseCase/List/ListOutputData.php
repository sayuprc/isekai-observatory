<?php

declare(strict_types=1);

namespace Song\Application\Viewer\UseCase\List;

use Song\Application\Viewer\Query\SongListItem;

readonly class ListOutputData
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
